<?php
require_once __DIR__ . '/includes/functions.php';
redirect(is_logged_in() ? 'dashboard.php' : 'auth/login.php');
