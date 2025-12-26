<?php
require_once 'models/FactorialModel.php';

class FactorialController
{
    private $model;

    public function __construct()
    {
        $this->model = new FactorialModel();
    }

    public function handleRequest()
    {
        $result = '';
        $input = '';

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $input = $_POST['number'];
            // Call the model to get the logic
            $result = $this->model->calculateFactorial($input);
        }

        // Load the view and pass variables
        include 'views/home.php';
    }
}
?>