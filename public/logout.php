<?php
require_once __DIR__ . '/../src/config.php';

initSession();
logout();
redirect('/');
