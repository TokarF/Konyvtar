<?php


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

    header("Location: /konyvek");
}

function bookHandler($urlParams)
{
    $pdo = getConnection();
    $book = getBookById($pdo, $urlParams["bookId"]);
    $borrows = getBorrowsByBookId($pdo, $urlParams["bookId"]);

    echo render("admin-wrapper.phtml", [
        "content" => render("konyv.phtml", [
            "book" => $book,
            "borrows" => $borrows
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

    header("Location: /konyvek");
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

function getAllActiveBooks($pdo, $editedBookId = null)
{
    $params = [];
    
    if ($editedBookId) {
        $sql = "SELECT * FROM books B WHERE isBorrowed = 0 OR B.id = :editedBookId";
        $params = [":editedBookId" => $editedBookId];
    } else {
        $sql = "SELECT * FROM books B WHERE isBorrowed = 0";
    }
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
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
