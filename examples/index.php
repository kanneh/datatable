<?php
const DEBUG = true;
require_once __DIR__ . '/includes/models.php';

$mdUsers->WHERE('id = ?')->searchParams = [$_GET['id'] ?? 1];
$mdUsers->process([
    'columns' => [
        [
            'data' => 'id',
            'searchable' => false
        ],
        [
            'data' => 'fullname',
            'searchable' => true
        ],
        // [
        //     'data' => 'email',
        //     'searchable' => true
        // ],
        // ['data' => 'phone'],
        ['data' => 'created_at']
    ],

])->json();
echo json_encode($_GET);