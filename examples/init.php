<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/includes/models.php';

$faker = Faker\Factory::create();

$mdUsers->create();
$mdStudents->create();

$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdStudents->process([
    'regno' => $faker->bothify('??##??##'),
    'userid' => $mdUsers->lastId(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdStudents->process([
    'regno' => $faker->bothify('??##??##'),
    'userid' => $mdUsers->lastId(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdStudents->process([
    'regno' => $faker->bothify('??##??##'),
    'userid' => $mdUsers->lastId(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdStudents->process([
    'regno' => $faker->bothify('??##??##'),
    'userid' => $mdUsers->lastId(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdStudents->process([
    'regno' => $faker->bothify('??##??##'),
    'userid' => $mdUsers->lastId(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdStudents->process([
    'regno' => $faker->bothify('??##??##'),
    'userid' => $mdUsers->lastId(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdStudents->process([
    'regno' => $faker->bothify('??##??##'),
    'userid' => $mdUsers->lastId(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
])->json();
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
])->json();
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
])->json();
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
])->json();
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
])->json();
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
])->json();
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
])->json();
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
])->json();
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
])->json();
$mdUsers->process([
    'username' => $faker->userName(),
    'fullname' => $faker->name(),
    'phone' => $faker->phoneNumber(),
    'email' => $faker->email(),
    'password' => $faker->password(),
    'KACTION' => 'create'
]);
$mdStudents->process([
    'regno' => $faker->bothify('??##??##'),
    'userid' => $mdUsers->lastId(),
    'KACTION' => 'create'
]);

// $mdUsers->process([])->json();

// $mdStudents->process([])->json();
