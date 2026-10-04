<?php
echo password_hash('Admin12345', PASSWORD_BCRYPT, ['cost' => 12]);
?>