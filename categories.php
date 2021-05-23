<?php

function categoriesHandler()
{
    $pdo = getConnection();
    $bookCategories = getAllCategories($pdo);

    echo render("admin-wrapper.phtml", [
        "content" => render("kategoriak.phtml", [
            "categories" => $bookCategories,
        ]),
    ]);
}

function categoryHandler($urlParams)
{
    $pdo = getConnection();
    $category = getCategoryById($pdo, $urlParams["categoryId"]);
    // echo "<pre>";
    // var_dump($category);
    $category["books"] = getBooksByCategoryId($pdo, $category["id"]);

    echo render("admin-wrapper.phtml", [
        "content" => render("kategoria.phtml", [
            "category" => $category
        ]),
    ]);
}

function createCategoryFormHandler()
{
    echo render("admin-wrapper.phtml", [
        "content" => render("uj-kategoria.phtml")
    ]);
}

function createCategoryHandler()
{
    $pdo = getConnection();
    $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (:name)");
    $stmt->execute([
        ":name" => $_POST["name"]
    ]);

    header("Location: /kategoriak");
}

function editcategoryHandler($urlParams)
{
    $pdo = getConnection();
    $category = getCategoryById($pdo, $urlParams["categoryId"]);

    echo render("admin-wrapper.phtml", [
        "content" => render("kategoria-szerkesztese.phtml", [
            "category" => $category
        ])
    ]);
}

function updatecategoryHandler($urlParams)
{
    $pdo = getConnection();

    $stmt = $pdo->prepare("UPDATE categories SET name = :name WHERE id = :id");
    $stmt->execute([
        ":name" => $_POST["name"],
        ":id" => $urlParams["categoryId"]
    ]);
    header("Location: /kategoria/" . $urlParams["categoryId"]);
}


function getAllCategories($pdo)
{
    $stmt = $pdo->prepare("SELECT * FROM categories");
    $stmt->execute();
    $bookCategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $bookCategories;
}

function getCategoryById($pdo, $id)
{
    $stmt = $pdo->prepare("SELECT * FROM categories C WHERE C.id = ?");
    $stmt->execute([$id]);
    $category = $stmt->fetch(PDO::FETCH_ASSOC);
    return $category;
}