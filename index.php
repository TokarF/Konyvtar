<?php

require './router.php';
require './slugifier.php';

$method = $_SERVER["REQUEST_METHOD"];
$parsed = parse_url($_SERVER['REQUEST_URI']);
$path = $parsed['path'];

// Útvonalak regisztrálása
$routes = [
    // [method, útvonal, handlerFunction],
    ['GET', '/', 'homeHandler'],
    ['GET', '/admin', 'adminDashboardHander'],
    ['GET', '/kategoriak', 'categoriesHandler'],
    ['GET', '/kategoria/{categoryId}', 'categoryHandler'],
    ['GET', '/kategoria-szerkesztese/{categoryId}', 'editcategoryHandler'],
    ['GET', '/uj-kategoria', 'createCategoryFormHandler'],
    ['GET', '/konyvek', 'booksHandler'],
    ['GET', '/konyv/{bookId}', 'bookHandler'],
    ['GET', '/uj-konyv', 'createBookFormHandler'],
    ['GET', '/konyv-szerkesztese/{bookId}', 'editbookHandler'],
    ['GET', '/uj-szerzo', 'createAuthorFormHandler'],
    ['GET', '/szerzo-szerkesztese/{authorId}', 'editAuthorHandler'],
    ['GET', '/szerzok', 'authorsHandler'],
    ['GET', '/szerzo/{authorId}', 'authorHandler'],
    ['POST', '/szerzo-szerkesztese/{authorId}', 'updateAuthorHandler'],
    ['POST', '/konyv-szerkesztese/{bookId}', 'updateBookHandler'],
    ['POST', '/uj-konyv', 'createBookHandler'],
    ['POST', '/uj-szerzo', 'createAuthorHandler'],
    ['POST', '/uj-kategoria', 'createCategoryHandler'],
    ['POST', '/kategoria-szerkesztese/{categoryId}', 'updatecategoryHandler'],

];

// Útvonalválasztó inicializálása
$dispatch = registerRoutes($routes);
$matchedRoute = $dispatch($method, $path);
$handlerFunction = $matchedRoute['handler'];
$handlerFunction($matchedRoute['vars']);

// Handler függvények deklarálása
function homeHandler()
{
    $pdo = getConnection();
    $bookCategories = getAllCategories($pdo);
    $books = getAllBooks($pdo);
    $authors = getAllAuthors($pdo);
    echo render("admin-wrapper.phtml", [
        "content" => render("admin.phtml", [
            "categories" => $bookCategories,
            "books" => $books,
            "authors" => $authors,
        ]),
    ]);
}

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
    header("Location: /szerzo/" . $pdo->lastInsertId());
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

function booksHandler()
{
    $pdo = getConnection();
    $books = getAllBooks($pdo);

    echo render("admin-wrapper.phtml", [
        "content" => render("konyvek.phtml", [
            "books" => $books
        ])
    ]);
}

function createBookFormHandler()
{
    $pdo = getConnection();
    $authors = getAllAuthors($pdo);
    $categories = getAllCategories($pdo);

    echo render("admin-wrapper.phtml", [
        "content" => render("uj-konyv.phtml", [
            "authors" => $authors,
            "categories" => $categories
        ])
    ]);
}

function createBookHandler()
{
    $pdo = getConnection();

    $stmt = $pdo->prepare("INSERT INTO books (author_id, title, description, pages, published) VALUES (:author_id, :title, :description, :pages, :published)");
    $stmt->execute([
        ":author_id" => $_POST["author_id"],
        ":title" => $_POST["title"],
        ":description" => $_POST["description"],
        ":pages" => $_POST["pages"],
        ":published" => $_POST["published"],
    ]);

    $newBookId = $pdo->lastInsertId();

    foreach ($_POST["categories"] as $category) {
        $stmt = $pdo->prepare("INSERT INTO books_categories (book_id, category_id) VALUES (:book_id, :category_id)");
        $stmt->execute([
            ":book_id" => $newBookId,
            ":category_id" => $category
        ]);
    }

    header("Location: /konyv/" . $newBookId);
}

function bookHandler($urlParams)
{
    $pdo = getConnection();
    $book = getBookById($pdo, $urlParams["bookId"]);

    echo render("admin-wrapper.phtml", [
        "content" => render("konyv.phtml", [
            "book" => $book
        ]),
    ]);
}

function editbookHandler($urlParams)
{
    $pdo = getConnection();
    $book = getBookById($pdo, $urlParams["bookId"]);
    $book["categories"] = explode(", ", $book["categories"]);
    $categories = getAllCategories($pdo);
    $authors = getAllAuthors($pdo);

    echo render("admin-wrapper.phtml", [
        "content" => render("konyv-szerkesztese.phtml", [
            "book" => $book,
            "categories" => $categories,
            "authors" => $authors
        ]),
    ]);
}

function updateBookHandler($urlParams)
{
    // echo "<pre>";
    // var_dump($_POST);
    // var_dump($urlParams);
    // foreach ($_POST["categories"] as $item) {
    //     echo $item;
    // }
    // exit;
    $pdo = getConnection();

    $stmt = $pdo->prepare("UPDATE books SET author_id = :author_id, title = :title, slug = :slug, description = :description, pages = :pages, published = :published WHERE id = :id");
    $stmt->execute([
        ":author_id" => $_POST["author_id"],
        ":title" => $_POST["title"],
        ":slug" => slugify($_POST["title"]),
        ":description" => $_POST["description"],
        ":pages" => $_POST["pages"],
        ":published" => $_POST["published"],
        ":id" => $urlParams["bookId"]
    ]);

    $stmt = $pdo->prepare("DELETE FROM books_categories WHERE book_id = :book_id");
    $stmt->execute([
        ":book_id" =>  $urlParams["bookId"]
    ]);

    foreach ($_POST["categories"] as $category) {
        $stmt = $pdo->prepare("INSERT INTO books_categories (book_id, category_id) VALUES (:book_id, :category_id)");
        $stmt->execute([
            ":book_id" => $urlParams["bookId"],
            ":category_id" => $category
        ]);
    }

    header("Location: /konyv/" . $urlParams["bookId"]);
}

function render($path, $params = [])
{
    ob_start();
    require __DIR__ . '/views/' . $path;
    return ob_get_clean();
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

function getAllBooks($pdo)
{
    $stmt = $pdo->prepare("SELECT B.*, A.name AS author, GROUP_CONCAT(C.name SEPARATOR ', ') AS categories FROM books B
    LEFT JOIN authors A ON A.id = B.author_id
    LEFT JOIN books_categories BC ON BC.book_id = B.id
    LEFT JOIN categories C ON C.id = BC.category_id
    GROUP BY B.title ORDER BY B.id");
    $stmt->execute();
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $books;
}

function getBooksByCategoryId($pdo, $categoryId)
{
    $stmt = $pdo->prepare("SELECT B.*, A.name AS author FROM books B
    LEFT JOIN authors A ON A.id = B.author_id
    LEFT JOIN books_categories BC ON BC.book_id = B.id
    LEFT JOIN categories C ON C.id = BC.category_id
    WHERE C.Id = ?");
    $stmt->execute([$categoryId]);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $books;
}

function getBooksByAuthorId($pdo, $authorId)
{
    $stmt = $pdo->prepare("SELECT B.*, GROUP_CONCAT(C.name SEPARATOR ', ') AS categories FROM books B
    LEFT JOIN authors A ON A.id = B.author_id
    LEFT JOIN books_categories BC ON BC.book_id = B.id
    LEFT JOIN categories C ON C.id = BC.category_id
    WHERE A.id = ?
    GROUP BY B.title
    ORDER BY B.id");
    $stmt->execute([$authorId]);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $books;
}

function getBookById($pdo, $bookId)
{
    $stmt = $pdo->prepare("SELECT B.*, A.name AS author, GROUP_CONCAT(C.name SEPARATOR ', ') AS categories FROM books B
    LEFT JOIN authors A ON A.id = B.author_id
    LEFT JOIN books_categories BC ON BC.book_id = B.id
    LEFT JOIN categories C ON C.id = BC.category_id
    WHERE B.id = ?");
    $stmt->execute([$bookId]);
    $book = $stmt->fetch(PDO::FETCH_ASSOC);
    return $book;
}


function getAllAuthors($pdo)
{
    $stmt = $pdo->prepare("SELECT * FROM authors");
    $stmt->execute();
    $authors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $authors;
}



function getConnection()
{
    return new PDO("mysql:dbname=library;host=localhost;charset=utf8", "root");
}
