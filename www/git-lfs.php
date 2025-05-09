<?php

include "../config.php";
include "functions.php";

class HTTPStatus {
    public $code;
    public $message;
}

class Response {
    public $httpstatus;
    public $response;
}

function make_response($code, $message) {
    $httpstatus = new HTTPStatus();
    $httpstatus->code = $code;
    $httpstatus->message = $message;

    $ret = new Response();
    $ret->httpstatus = $httpstatus;
    $ret->response = NULL;

    return $ret;
}

// A very specific content type headers must be present.
define("CONTENT_TYPE", "application/vnd.git-lfs+json");

function GitLFSServer($namespace, $path, $session) {
    if (
        explode(";", $_SERVER["CONTENT_TYPE"] ?? "")[0] != CONTENT_TYPE ||
        explode(";", $_SERVER["HTTP_ACCEPT"] ?? "")[0] != CONTENT_TYPE
    ) {
        $ret = make_response(406, "The Accept header needs to be " . CONTENT_TYPE . ".");
        return $ret;
    }

    switch($namespace) {
        case "data":
            define("publicurl", publicdataurl);
            break;
        case "data-unique":
            define("publicurl", publicdatauniqueurl);
            break;
        default:
            $ret = make_response(400, "Bad Request (invalid namespace)");
            $ret->response = [
                "message" => "Bad Request (the queried namespace is invalid)",
            ];
            return $ret;
    }

    // We do not support Locking API.
    if ($path == "locks/verify") {
        $ret = make_response(403, "Read-only Git LFS server");
        $ret->response = ["message" => "This is a read-only Git LFS server"];
        return $ret;
    }

    // This implements Git LFS Batch API, only accept appropriate URI.
    if ($path != "objects/batch") {
        $ret = make_response(400, "Bad Request (unsupported URI)");
        $ret->response = [
            "message" => "Bad Request (the queried URI is not supported)",
        ];
        return $ret;
    }

    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        $ret = make_response(400, "Bad Request (not a POST verb)");
        $ret->response = [
            "message" => "Bad Request (all Batch API requests use the POST verb)",
        ];
        return $ret;
    }

    $in = file_get_contents("php://input");
    $request = json_decode($in, true);

    $request["hash_algo"] = $request["hash_algo"] ?? "sha256";
    $request["transfers"] = $request["transfers"] ?? ["basic"];

    if (!array_key_exists("operation", $request)) {
        $ret = make_response(422, "Validation error");
        $ret->response = [
            "message" => "Validation error (operation not specified)",
        ];
        return $ret;
    }

    if ($request["operation"] == "upload") {
        $ret = make_response(403, "Read-only Git LFS server");
        $ret->response = ["message" => "This is a read-only Git LFS server"];
        return $ret;
    }

    if ($request["operation"] != "download") {
        $ret = make_response(422, "Validation error");
        $ret->response = [
            "message" => "Validation error (invalid operation specified)",
        ];
        return $ret;
    }

    if ($request["hash_algo"] != "sha256") {
        $ret = make_response(409, "Unsupported hash algorithm");
        $ret->response = ["message" => "Hash algorithm must be sha256"];
        return $ret;
    }

    if (!in_array("basic", $request["transfers"], true)) {
        $ret = make_response(409, "Unsupported transfer adapter");
        $ret->response = ["message" => "Basic transfer adapter must be supported"];
        return $ret;
    }

    if (!array_key_exists("objects", $request)) {
        $ret = make_response(422, "Validation error");
        $ret->response = ["message" => "Validation error (no objects specified)"];
        return $ret;
    }

    foreach ($request["objects"] as $object) {
        if (
            !(count($object) == 2) ||
            !array_key_exists("oid", $object) ||
            !array_key_exists("size", $object) ||
            !is_int($object["size"]) ||
            !($object["size"] >= 0) ||
            !is_string($object["oid"]) ||
            !(strlen($object["oid"]) == 64) ||
            !ctype_xdigit($object["oid"])
        ) {
            $ret = make_response(422, "Validation error");
            $ret->response = [
                "message" => "Validation error (bad object request)",
            ];
            return $ret;
        }
    }

    // Okay, this looks like a sensible request.

    $response = [];
    $response["transfer"] = "basic";
    $response["hash_algo"] = "sha256";
    $response["objects"] = [];

    $allraws = raw_getalldata();
    $raws = [];
    foreach ($allraws as $raw) {
        if ($raw["validated"] == "1") {
            $filename = (new RawEntry($raw))->getOutputPath();
            $raws[$raw["checksum"] . "/" . $raw["filesize"]] = $filename;
        }
    }

    foreach ($request["objects"] as $object) {
        $objectResponse = [
            "oid" => $object["oid"],
            "size" => $object["size"],
        ];
        $actions = [];
        $key = $object["oid"] . "/" . $object["size"];
        if (array_key_exists($key, $raws)) {
            $uri = $raws[$key];
            $objectResponse["authenticated"] = true;
            $actions["download"] = [
                "href" => publicurl . "/" . $uri,
                "header" => [
                    "X-RPU-Git-LFS-Session-ID" => $session,
                ],
            ];
        } else {
            $actions["error"] = [
                "code" => 404,
                "message" => "Object does not exist",
            ];
        }
        $objectResponse["actions"] = $actions;
        $response["objects"][] = $objectResponse;
    }

    $ret = make_response(200, "OK");
    $ret->response = $response;

    return $ret;
}

list(, $namespace, $path) = explode("/", $_SERVER["PATH_INFO"] ?? "", 3);
$session = guidv4();

$response = GitLFSServer($namespace, $path, $session);

header("HTTP/1.1 ".$response->httpstatus->code." ".$response->httpstatus->message);

if(!is_null($response->response)) {
   header("Content-Type: " . CONTENT_TYPE);
   echo json_encode($response->response);
}

influxPoint("git-lfs",
            [
                "namespace" => $namespace,
                "code" => $response->httpstatus->code
            ],
            [
                "session" => $session
            ]);
