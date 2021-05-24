<?php

function readersHandler()
{
    $pdo = getConnection();
    $readers = getAllReaders($pdo);

    echo render("admin-wrapper.phtml", [
        "content" => render("olvasok.phtml", [
            "readers" => $readers,
        ]),
    ]);
}

function readerHandler($urlParams)
{
    $pdo = getConnection();
    $reader = getReaderById($pdo, $urlParams["readerId"]);

    echo render("admin-wrapper.phtml", [
        "content" => render("olvaso.phtml", [
            "reader" => $reader,
        ]),
    ]);
}

function editReaderHandler($urlParams)
{
    $pdo = getConnection();
    $reader = getReaderById($pdo, $urlParams["readerId"]);

    echo render("admin-wrapper.phtml", [
        "content" => render("olvaso-szerkesztese.phtml", [
            "reader" => $reader,
        ]),
    ]);
}

function updateReaderHandler($urlParams)
{
    $pdo = getConnection();
    $stmt = $pdo->prepare("UPDATE readers SET firstName = :firstName, lastName = :lastName, email = :email, mobile = :mobile, address = :address WHERE id = :id");
    $stmt->execute([
        ":firstName" => $_POST["firstName"],
        ":lastName" => $_POST["lastName"],
        "email" => $_POST["email"],
        "mobile" => $_POST["mobile"],
        "address" => $_POST["address"],
        "id" => $urlParams["readerId"],
    ]);

    header("Location: /olvaso/" . $urlParams["readerId"]);
}

function createReaderFormHandler()
{
    echo render("admin-wrapper.phtml", [
        "content" => render("uj-olvaso.phtml")
    ]);
}

function createReaderHandler()
{
    $pdo = getConnection();
    $stmt = $pdo->prepare("INSERT INTO readers (firstName, lastName, email, mobile, address) VALUES (:firstName, :lastName, :email, :mobile, :address)");
    $stmt->execute([
        ":firstName" => $_POST["firstName"],
        ":lastName" => $_POST["lastName"],
        "email" => $_POST["email"],
        "mobile" => $_POST["mobile"],
        "address" => $_POST["address"],
    ]);
    
    header("Location: /olvasok");
}

function getAllReaders($pdo)
{
    $stmt = $pdo->prepare("SELECT * FROM readers");
    $stmt->execute();
    $readers = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $readers;
} 

function getReaderById($pdo, $readerId)
{
    $stmt = $pdo->prepare("SELECT * FROM readers R WHERE R.id = ?");
    $stmt->execute([$readerId]);
    $reader = $stmt->fetch(PDO::FETCH_ASSOC);
    return $reader;
}