<?php

function borrowsHandler()
{
    $pdo = getConnection();
    $borrows = getAllBorrows($pdo);

    echo render("admin-wrapper.phtml", [
        "content" => render("kolcsonzesek.phtml", [
            "borrows" => $borrows
        ])
    ]);
}

function bookReturnHandler($urlparams)
{
    $pdo = getConnection();
    $borrow = getBorrowById($pdo, $urlparams["borrowId"]);

    $stmt = $pdo->prepare("UPDATE borrows SET return_date = :return_date WHERE id = :id");
    $stmt->execute([
        ":return_date" => date('Y-m-d'),
        ":id" => $urlparams["borrowId"]
    ]);

    $stmt = $pdo->prepare("UPDATE books SET isBorrowed = 0 WHERE id = :book_id");
    $stmt->execute([":book_id" => $borrow["book_id"]]);

    header("location:".$_SERVER['HTTP_REFERER']);
}

function editBorrowHandler($urlparams)
{
    $pdo = getConnection();
    $borrow = getBorrowById($pdo, $urlparams["borrowId"]);
    $readers = getAllReaders($pdo);
    $books = getAllActiveBooks($pdo, $borrow["book_id"]);


    echo render("admin-wrapper.phtml", [
        "content" => render("kolcsonzes-szerkesztese.phtml", [
            "borrow" => $borrow,
            "readers" => $readers,
            "books" => $books
        ])
    ]);
}

function updateBorrowHandler($urlparams)
{
    // echo "<pre>";
    // var_dump($_POST);
    // echo gettype($_POST["return_date"]);
    // exit;
    $pdo = getConnection();

    $stmt = $pdo->prepare("UPDATE borrows SET reader_id = :reader_id, book_id = :book_id, borrow_date = :borrow_date, due_date = :due_date, return_date = :return_date WHERE id = :id");
    $stmt->execute([
        ":reader_id" => $_POST["reader_id"],
        ":book_id" => $_POST["book_id"],
        ":borrow_date" => $_POST["borrow_date"],
        ":due_date" => $_POST["due_date"],
        ":return_date" => !empty($_POST["return_date"]) ? $_POST["return_date"] : null,
        ":id" => $urlparams["borrowId"],
    ]);

    if (empty($_POST["return_date"])) {
        $stmt = $pdo->prepare("UPDATE books SET isBorrowed = 1 WHERE id = :book_id");
        $stmt->execute([":book_id" => $_POST["book_id"]]);
    }
    else {
        $stmt = $pdo->prepare("UPDATE books SET isBorrowed = 0 WHERE id = :book_id");
        $stmt->execute([":book_id" => $_POST["book_id"]]);
    }

    header("Location: /kolcsonzesek");
}

function createBorrowFormHandler()
{
    $pdo = getConnection();
    $readers = getAllReaders($pdo);
    $books = getAllActiveBooks($pdo);

    echo render("admin-wrapper.phtml", [
        "content" => render("uj-kolcsonzes.phtml", [
            "readers" => $readers,
            "books" => $books
        ])
    ]);
}

function createBorrowHandler()
{
    // echo "<pre>";
    // var_dump($_POST);
    // exit;
    $pdo = getConnection();

    $stmt = $pdo->prepare("INSERT INTO borrows (reader_id, book_id, borrow_date, due_date) VALUES (:reader_id, :book_id, :borrow_date, :due_date)");
    $stmt->execute([
        ":reader_id" => $_POST["reader_id"],
        ":book_id" => $_POST["book_id"],
        ":borrow_date" => $_POST["borrow_date"],
        ":due_date" => $_POST["due_date"],
    ]);

    $stmt = $pdo->prepare("UPDATE books SET isBorrowed = 1 WHERE id = :book_id");
    $stmt->execute([":book_id" => $_POST["book_id"]]);

    header("Location: /kolcsonzesek");

}

function getAllBorrows($pdo)
{
    $stmt = $pdo->prepare("SELECT B.*, CONCAT(R.lastname, ' ', R.firstname) AS reader, BK.id AS book_id, BK.title AS book FROM borrows B 
    LEFT JOIN readers R ON R.id = B.reader_id
    LEFT JOIN books BK ON BK.id = B.book_id");
    $stmt->execute();
    $borrows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $borrows;
}

function getAllActiveBorrows($pdo)
{
    $stmt = $pdo->prepare("SELECT B.*, CONCAT(R.lastname, ' ', R.firstname) AS reader, BK.id AS book_id, BK.title AS book FROM borrows B 
    LEFT JOIN readers R ON R.id = B.reader_id
    LEFT JOIN books BK ON BK.id = B.book_id
    WHERE return_date IS NULL");
    $stmt->execute();
    $borrows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $borrows;
}

function getBorrowById($pdo, $id)
{
    $stmt = $pdo->prepare("SELECT B.*, BK.title AS book_title, CONCAT(R.lastName, ' ', R.firstName) AS reader_name FROM borrows B
    LEFT JOIN books BK ON BK.id = B.book_id
    LEFT JOIN readers R ON R.id = B.reader_id
    WHERE B.id = ?");
    $stmt->execute([$id]);

    $borrow = $stmt->fetch(PDO::FETCH_ASSOC);

    return $borrow;
}

function getBorrowsByReaderId($pdo, $readerId)
{
    $stmt = $pdo->prepare("SELECT B.*, BK.title AS book_title, CONCAT(R.lastName, ' ', R.firstName) AS reader_name FROM borrows B
    LEFT JOIN books BK ON BK.id = B.book_id
    LEFT JOIN readers R ON R.id = B.reader_id
    WHERE B.reader_id = ?");
    $stmt->execute([$readerId]);

    $borrows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $borrows;
}

function getBorrowsByBookId($pdo, $bookId)
{
    $stmt = $pdo->prepare("SELECT B.*, BK.title AS book_title, CONCAT(R.lastName, ' ', R.firstName) AS reader_name FROM borrows B
    LEFT JOIN books BK ON BK.id = B.book_id
    LEFT JOIN readers R ON R.id = B.reader_id
    WHERE B.book_id = ?");
    $stmt->execute([$bookId]);

    $borrows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return $borrows;
}