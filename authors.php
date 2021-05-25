<?php 

function authorsHandler()
{
    $pdo = getConnection();
    $authors = getAllAuthors($pdo);

    echo render("admin-wrapper.phtml", [
        "content" => render("szerzok.phtml", [
            "authors" => $authors,
        ]),
    ]);
}

function authorHandler($urlParams)
{
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM authors A WHERE A.id = ?");
    $stmt->execute([$urlParams["authorId"]]);
    $author = $stmt->fetch(PDO::FETCH_ASSOC);
    $author["books"] = getBooksByAuthorId($pdo, $author["id"]);

    // echo "<pre>";
    // var_dump($author);
    // exit;
    echo render("admin-wrapper.phtml", [
        "content" => render("szerzo.phtml", [
            "author" => $author,
        ]),
    ]);
}

function createAuthorFormHandler()
{
    echo render("admin-wrapper.phtml", [
        "content" => render("uj-szerzo.phtml")
    ]);
}

function createAuthorHandler()
{
    $pdo = getConnection();

    $stmt = $pdo->prepare("INSERT INTO authors (name, bio) VALUES (:name, :bio)");
    $stmt->execute([
        ":name" => $_POST["name"],
        ":bio" => $_POST["bio"],
    ]);
    header("Location: /szerzok");
}

function editAuthorHandler($urlParams)
{
    $pdo = getConnection();
    $stmt = $pdo->prepare("SELECT * FROM authors A WHERE A.id = ?");
    $stmt->execute([$urlParams["authorId"]]);
    $author = $stmt->fetch(PDO::FETCH_ASSOC);

    echo render("admin-wrapper.phtml", [
        "content" => render("szerzo-szerkesztese.phtml", [
            "author" => $author,
        ]),
    ]);
}

function updateAuthorHandler($urlParams)
{
    $pdo = getConnection();
    $stmt = $pdo->prepare("UPDATE authors SET name = :name, bio = :bio WHERE id = :id  ");
    $stmt->execute([
        ":name" => $_POST["name"],
        ":bio" => $_POST["bio"],
        ":id" => $urlParams["authorId"],
    ]);
    header("Location: /szerzo/" . $urlParams["authorId"]);
}

function getAllAuthors($pdo)
{
    $stmt = $pdo->prepare("SELECT * FROM authors");
    $stmt->execute();
    $authors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $authors;
}
