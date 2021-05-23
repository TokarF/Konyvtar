<?php

require './router.php';
require './slugifier.php';
require './authors.php';
require './categories.php';
require './books.php';

$method = $_SERVER["REQUEST_METHOD"];
$parsed = parse_url($_SERVER['REQUEST_URI']);
$path = $parsed['path'];

// Útvonalak regisztrálása
$routes = [
    // [method, útvonal, handlerFunction],
    ['GET', '/', 'homeHandler'],
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
    ['GET', '/olvasok', 'readersHandler'],
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
