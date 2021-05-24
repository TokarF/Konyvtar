<?php

require './router.php';
require './slugifier.php';
require './authors.php';
require './categories.php';
require './books.php';
require './readers.php';

$method = $_SERVER["REQUEST_METHOD"];
$parsed = parse_url($_SERVER['REQUEST_URI']);
$path = $parsed['path'];

// Útvonalak regisztrálása
$routes = [
    // [method, útvonal, handlerFunction],
    ['GET', '/', 'homeHandler'],

    // Kategóriák
    ['GET', '/kategoriak', 'categoriesHandler'],
    ['GET', '/kategoria/{categoryId}', 'categoryHandler'],
    ['GET', '/uj-kategoria', 'createCategoryFormHandler'],
    ['POST', '/uj-kategoria', 'createCategoryHandler'],
    ['GET', '/kategoria-szerkesztese/{categoryId}', 'editcategoryHandler'],
    ['POST', '/kategoria-szerkesztese/{categoryId}', 'updatecategoryHandler'],

    // Könyvek
    ['GET', '/konyvek', 'booksHandler'],
    ['GET', '/konyv/{bookId}', 'bookHandler'],
    ['GET', '/uj-konyv', 'createBookFormHandler'],
    ['POST', '/uj-konyv', 'createBookHandler'],
    ['GET', '/konyv-szerkesztese/{bookId}', 'editbookHandler'],
    ['POST', '/konyv-szerkesztese/{bookId}', 'updateBookHandler'],

    // Szerzők
    ['GET', '/szerzok', 'authorsHandler'],
    ['GET', '/szerzo/{authorId}', 'authorHandler'],
    ['GET', '/uj-szerzo', 'createAuthorFormHandler'],
    ['POST', '/uj-szerzo', 'createAuthorHandler'],
    ['GET', '/szerzo-szerkesztese/{authorId}', 'editAuthorHandler'],
    ['POST', '/szerzo-szerkesztese/{authorId}', 'updateAuthorHandler'],

    // Olvasók
    ['GET', '/olvasok', 'readersHandler'],
    ['GET', '/olvaso/{readerId}', 'readerHandler'],
    ['GET', '/olvaso-szerkesztese/{readerId}', 'editReaderHandler'],
    ['POST', '/olvaso-szerkesztese/{readerId}', 'updateReaderHandler'],
    ['GET', '/uj-olvaso', 'createReaderFormHandler'],
    ['POST', '/uj-olvaso', 'createReaderHandler'],

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
    $readers = getAllReaders($pdo);

    echo render("admin-wrapper.phtml", [
        "content" => render("admin.phtml", [
            "categories" => $bookCategories,
            "books" => $books,
            "authors" => $authors,
            "readers" => $readers
        ]),
    ]);
}

function render($path, $params = [])
{
    ob_start();
    require __DIR__ . '/views/' . $path;
    return ob_get_clean();
}

function getConnection()
{
    return new PDO("mysql:dbname=library;host=localhost;charset=utf8", "root");
}
