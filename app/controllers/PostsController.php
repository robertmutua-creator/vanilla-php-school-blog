<?php
$model = __DIR__ . "/../models/Posts.php";
require_once $model;
require_once __DIR__ . "/../models/Users.php";
$userDir = $_SESSION['user']['role'] ?? '';

class PostsController
{
    private $model;
    private $users;
    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        $this->model = new Posts();
        $this->users = new Users();
    }





    // index function
    public function index()
    {
        if ($_SESSION['user']['role'] === 'school') {
            $school = $_SESSION['user']['id'];
        } else {
            $school = $_SESSION['user']['school_id'];
        }
        return $this->model->retrieve($school);
    }

    // create post function
    public function create()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userDir = $_SESSION['user']['role'] ?? 'user';
        $user_id = $_SESSION['user']['id'] ?? null;

        if (!$user_id) {
            $_SESSION["error"] = "You must be logged in to create a post.";
            header("Location: /vanilla_blog/login");
            exit();
        }

        $content = trim($_POST["content"] ?? '');
        $file_path = null;

        // ✅ File upload check
        if (!empty($_FILES["file"]["name"])) {
            $file_size = $_FILES["file"]["size"];
            $file_type = $_FILES["file"]["type"];
            $file_tmp  = $_FILES["file"]["tmp_name"];
            $file_name = time() . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "_", basename($_FILES["file"]["name"]));

            // 🔹 Size validation (5MB)
            if ($file_size > 5 * 1024 * 1024) {
                $_SESSION["error"] = "File size exceeds 5MB limit.";
                header("Location: /vanilla_blog/{$userDir}");
                exit();
            }

            // 🔹 Type validation (images + PDF)
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            if (!in_array($file_type, $allowed_types)) {
                $_SESSION["error"] = "Only JPG, PNG, GIF images and PDF files are allowed.";
                header("Location: /vanilla_blog/{$userDir}");
                exit();
            }

            // 🔹 Upload directory
            $target_dir = __DIR__ . "/../../public/uploads/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0755, true);
            }

            // 🔹 Final path
            $target_file = $target_dir . $file_name;

            if (move_uploaded_file($file_tmp, $target_file)) {
                $file_path = "/uploads/" . $file_name;
            } else {
                $_SESSION["error"] = "Sorry, there was an error uploading your file.";
                header("Location: /vanilla_blog/{$userDir}");
                exit();
            }
        }

        // ✅ Either text OR file must exist
        if (empty($content) && !$file_path) {
            $_SESSION["error"] = "Post cannot be empty. Please add text or upload a file.";
            header("Location: /vanilla_blog/{$userDir}");
            exit();
        }

        // ✅ Save post
        $response = $this->model->postContent($user_id, $content, $file_path);
        if ($response > 0) {
            $_SESSION["success"] = "Post created successfully.";
            header("Location: /vanilla_blog/{$userDir}/posts");
            exit();
        } else {
            $_SESSION["error"] = "Failed to create post.";
            header("Location: /vanilla_blog/{$userDir}");
            exit();
        }
    }


    // edit post function
    public function getPost($code)
    {
        global $userDir;
        // $post=$_POST['post'] ?? null;
        $post = $this->model->getById($code);
        if ($post === null) {
            $_SESSION["error"] = "Post not found!";
            header("Location: /vanilla_blog/{$userDir}/posts");
            exit();
        }
        return $post;
    }

    // delete post and its file if exists in server
    public function delete($post)
    {
        $userDir = $_SESSION['user']['role'];
        if (empty($post)) {
            $_SESSION["error"] = "Post ID is required!";
            header("Location: /vanilla_blog/{$userDir}/posts");
            exit();
        }

        // fetch post to get image path
        $existing_post = $this->model->getById($post);
        if ($existing_post && !empty($existing_post['image_path'])) {
            $file_path = __DIR__ . "/../../public" . $existing_post['image_path'];
            if (file_exists($file_path)) {
                unlink($file_path); // delete the file
            }
        }

        $response = $this->model->deleteById($post);
        if ($response > 0) {
            $_SESSION["success"] = "Post deleted successfully.";
            header("Location: /vanilla_blog/{$userDir}/posts");
            exit();
        } else {
            $_SESSION["error"] = "Failed to delete post.";
            header("Location: /vanilla_blog/{$userDir}/posts");
            exit();
        }
    }
}
