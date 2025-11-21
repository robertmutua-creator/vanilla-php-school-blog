<?php
// ========================
// Error reporting & session
// ========================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ========================
// Process URL
// ========================
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$request = trim($request, '/');

// Update base to current folder
$base = 'vanilla_blog';

// Remove base folder from request if present
if ($request === $base) {
    $request = '';
} elseif (strpos($request, $base . '/') === 0) {
    $request = substr($request, strlen($base) + 1);
}

// ========================
// Routes
// ========================
switch (true) {

    // ----- Auth -----
    case $request === '' || $request === 'home' || $request === 'login':
        require __DIR__ . '/public/login.php';
        break;

    case $request === 'register':
        require __DIR__ . '/public/register.php';
        break;

    case $request === 'signup' && $_SERVER['REQUEST_METHOD'] === 'POST':
        require_once __DIR__ . '/app/controllers/UsersController.php';
        $controller = new UsersController();
        $controller->create();
        break;

    case $request === 'authenticate' && $_SERVER['REQUEST_METHOD'] === 'POST':
        require_once __DIR__ . '/app/controllers/UsersController.php';
        $controller = new UsersController();
        $controller->login();
        break;

    case $request === 'logout':
        require_once __DIR__ . "/app/controllers/UsersController.php";
        (new UsersController())->logout();
        break;

    // ----- Posts listing -----
    case preg_match('#^(school|teacher|student)(/posts)?$#', $request, $matches):
        $type = strtolower($matches[1]);
        require_once __DIR__ . "/app/controllers/PostsController.php";
        $posts = new PostsController();
        $posts->index();

        $viewFile = __DIR__ . "/app/views/{$type}s/index.php";
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            http_response_code(404);
            require __DIR__ . '/public/404.php';
        }
        break;

    // ----- Create posts -----
    case preg_match('#^(school|teacher|student)/create-post$#', $request, $matches):
        require_once __DIR__ . "/app/controllers/PostsController.php";
        (new PostsController())->create();
        break;

    // ----- View post & comments -----
    case preg_match('#^(school|teacher|student)/posts/(\d+)$#', $request, $matches):
        $type = strtolower($matches[1]);
        $id = $matches[2];

        require_once __DIR__ . "/app/controllers/PostsController.php";
        $posts = new PostsController();
        $post  = $posts->getPost($id);

        require_once __DIR__ . "/app/controllers/CommentsController.php";
        $comments = new CommentsController();
        $comments = $comments->show($id);

        $viewFile = __DIR__ . "/app/views/{$type}s/comments.php";
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            http_response_code(404);
            require __DIR__ . '/public/404.php';
        }
        break;

    // ----- Delete posts -----
    case preg_match('#^(school|teacher|student)/post/delete/(\d+)$#', $request, $matches):
        require_once __DIR__ . "/app/controllers/PostsController.php";
        (new PostsController())->delete($matches[2]);
        break;

    // ----- Comments actions -----
    case preg_match('#^(school|teacher|student)/post/comment/send$#', $request):
        require_once __DIR__ . "/app/controllers/CommentsController.php";
        (new CommentsController())->comment();
        break;

    case preg_match('#^(school|teacher|student)/comment/delete/(\d+)$#', $request, $matches):
        require_once __DIR__ . "/app/controllers/CommentsController.php";
        (new CommentsController())->delete($matches[2]);
        break;

    // ----- Users management -----
    case preg_match('#^(school|teacher|student)/users$#', $request, $matches):
        $type = strtolower($matches[1]);
        require_once __DIR__ . "/app/controllers/UsersController.php";
        $users = new UsersController();
        $usersList = $users->index();

        $viewFile = __DIR__ . "/app/views/{$type}s/users.php";
        if (file_exists($viewFile)) {
            require $viewFile;
        } else {
            http_response_code(404);
            require __DIR__ . '/public/404.php';
        }
        break;

    case preg_match('#^(school|teacher)/new-user$#', $request, $matches):
        require_once __DIR__ . "/app/controllers/UsersController.php";
        (new UsersController())->create();
        break;

    case $request === 'users/settings':
        require_once __DIR__ . '/app/controllers/UsersController.php';
        $users = new UsersController();
        $users->settings();
        break;

    case $request === 'users/update' && $_SERVER['REQUEST_METHOD'] === 'POST':
        require_once __DIR__ . '/app/controllers/UsersController.php';
        $users = new UsersController();
        $users->update();
        break;

    case $request === 'users/delete':
        require_once __DIR__ . "/app/controllers/UsersController.php";
        $users = new UsersController();
        $users->delete();
        break;

    // ----- 404 fallback -----
    default:
        http_response_code(404);
        require __DIR__ . '/public/404.php';
        break;
}
