<?php
declare(strict_types=1);

require dirname(__DIR__) . '/app/bootstrap.php';

(new App\Controllers\PortfolioController(new App\Models\Content()))->handle();
