<?php

include "../config.php";
include "functions.php";

// A very specific content type headers must be present.
define("CONTENT_TYPE", "application/vnd.git-lfs+json");
if (
    explode(";", $_SERVER["CONTENT_TYPE"] ?? "")[0] != CONTENT_TYPE ||
    explode(";", $_SERVER["HTTP_ACCEPT"] ?? "")[0] != CONTENT_TYPE
) {
    header("HTTP/1.1 406 The Accept header needs to be " . CONTENT_TYPE . ".");
    exit();
}

header("Content-Type: " . CONTENT_TYPE);

list(, $namespace, $path) = explode("/", $_SERVER["PATH_INFO"] ?? "", 3);

switch($namespace) {
    case "data":
        define("publicurl", publicdataurl);
        break;
    case "data-unique":
        define("publicurl", publicdatauniqueurl);
        break;
    default:
        header("HTTP/1.1 400 Bad Request (invalid namespace)");
        echo json_encode([
            "message" => "Bad Request (the queried namespace is invalid)",
        ]);
        exit();
}

// We do not support Locking API.
if ($path == "locks/verify") {
    header("HTTP/1.1 403 Read-only Git LFS server");
    echo json_encode(["message" => "This is a read-only Git LFS server"]);
    exit();
}

// This implements Git LFS Batch API, only accept appropriate URI.
if ($path != "objects/batch") {
    header("HTTP/1.1 400 Bad Request (unsupported URI)");
    echo json_encode([
        "message" => "Bad Request (the queried URI is not supported)",
    ]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("HTTP/1.1 400 Bad Request (not a POST verb)");
    echo json_encode([
        "message" => "Bad Request (all Batch API requests use the POST verb)",
    ]);
    exit();
}

$in = file_get_contents("php://input");
$request = json_decode($in, true);

$request["hash_algo"] = $request["hash_algo"] ?? "sha256";
$request["transfers"] = $request["transfers"] ?? ["basic"];

if (!array_key_exists("operation", $request)) {
    header("HTTP/1.1 422 Validation error");
    echo json_encode([
        "message" => "Validation error (operation not specified)",
    ]);
    exit();
}

if ($request["operation"] == "upload") {
    header("HTTP/1.1 403 Read-only Git LFS server");
    echo json_encode(["message" => "This is a read-only Git LFS server"]);
    exit();
}

if ($request["operation"] != "download") {
    header("HTTP/1.1 422 Validation error");
    echo json_encode([
        "message" => "Validation error (invalid operation specified)",
    ]);
    exit();
}

if ($request["hash_algo"] != "sha256") {
    header("HTTP/1.1 409 Unsupported hash algorithm");
    echo json_encode(["message" => "Hash algorithm must be sha256"]);
    exit();
}

if (!in_array("basic", $request["transfers"], true)) {
    header("HTTP/1.1 409 Unsupported transfer adapter");
    echo json_encode(["message" => "Basic transfer adapter must be supported"]);
    exit();
}

if (!array_key_exists("objects", $request)) {
    header("HTTP/1.1 422 Validation error");
    echo json_encode(["message" => "Validation error (no objects specified)"]);
    exit();
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
        header("HTTP/1.1 422 Validation error");
        echo json_encode([
            "message" => "Validation error (bad object request)",
        ]);
        exit();
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
        $raws[$raw["checksum"] . "/" . $raw["filesize"]] = get_raw_pretty_name(
            $raw,
            $make,
            $model
        );
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
        $actions["download"] = ["href" => publicurl . "/" . $uri];
    } else {
        $actions["error"] = [
            "code" => 404,
            "message" => "Object does not exist",
        ];
    }
    $objectResponse["actions"] = $actions;
    $response["objects"][] = $objectResponse;
}

echo json_encode($response);
