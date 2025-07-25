<?php

// Admins

// Find all admins, ordered last_name, first_name
function find_all_admins()
{
    global $db;

    return $db->query('SELECT * FROM admins ORDER BY last_name, first_name', PDO::FETCH_ASSOC);
}

function find_admin_by_id($id)
{
    global $db;

    $statement = $db->prepare('SELECT * FROM admins WHERE id=:id LIMIT 1');

    $statement->execute([
        'id' => $id,
    ]);

    return $statement->fetch(PDO::FETCH_ASSOC); // returns an assoc. array
}

function find_admin_by_username($username)
{
    global $db;

    $statement = $db->prepare('SELECT * FROM admins WHERE username=:username LIMIT 1');
    $statement->execute([
        'username' => $username,
    ]);

    return $statement->fetch(PDO::FETCH_ASSOC); // returns an assoc. array
}

function validate_admin($admin, $options = [])
{
    $password_required = $options['password_required'] ?? true;

    if (is_blank($admin['first_name'])) {
        $errors[] = "First name cannot be blank.";
    } elseif (!has_length($admin['first_name'], array('min' => 2, 'max' => 255))) {
        $errors[] = "First name must be between 2 and 255 characters.";
    }

    if (is_blank($admin['last_name'])) {
        $errors[] = "Last name cannot be blank.";
    } elseif (!has_length($admin['last_name'], array('min' => 2, 'max' => 255))) {
        $errors[] = "Last name must be between 2 and 255 characters.";
    }

    if (is_blank($admin['email'])) {
        $errors[] = "Email cannot be blank.";
    } elseif (!has_length($admin['email'], array('max' => 255))) {
        $errors[] = "Last name must be less than 255 characters.";
    } elseif (!has_valid_email_format($admin['email'])) {
        $errors[] = "Email must be a valid format.";
    }

    if (is_blank($admin['username'])) {
        $errors[] = "Username cannot be blank.";
    } elseif (!has_length($admin['username'], array('min' => 8, 'max' => 255))) {
        $errors[] = "Username must be between 8 and 255 characters.";
    } elseif (!has_unique_username($admin['username'], $admin['id'] ?? 0)) {
        $errors[] = "Username not allowed. Try another.";
    }

    if ($password_required) {
//        if (is_blank($admin['password'])) {
//            $errors[] = "Password cannot be blank.";
//        } elseif (!has_length($admin['password'], array('min' => 12))) {
//            $errors[] = "Password must contain 12 or more characters";
//        } elseif (!preg_match('/[A-Z]/', $admin['password'])) {
//            $errors[] = "Password must contain at least 1 uppercase letter";
//        } elseif (!preg_match('/[a-z]/', $admin['password'])) {
//            $errors[] = "Password must contain at least 1 lowercase letter";
//        } elseif (!preg_match('/[0-9]/', $admin['password'])) {
//            $errors[] = "Password must contain at least 1 number";
//        } elseif (!preg_match('/[^A-Za-z0-9\s]/', $admin['password'])) {
//            $errors[] = "Password must contain at least 1 symbol";
//        } elseif ($admin['username'] == $admin['password']) {
//            $errors[] = "Username and password must be different";
//        }

        if (is_blank($admin['confirm_password'])) {
            $errors[] = "Confirm password cannot be blank.";
        } elseif ($admin['password'] !== $admin['confirm_password']) {
            $errors[] = "Password and confirm password must match.";
        }
    }

    return $errors;
}

function insert_admin($admin)
{
    global $db;

    $errors = validate_admin($admin);
    if (!empty($errors)) {
        return $errors;
    }

    $hashed_password = password_hash($admin['password'], PASSWORD_BCRYPT);

    $statement = $db->prepare('INSERT INTO admins (first_name, last_name, email, username, hashed_password) VALUES (
        :firstName, 
        :lastName, 
        :email, 
        :username, 
        :password
    )');

    $executed = $statement->execute([
        'firstName' => $admin['first_name'],
        'lastName' => $admin['last_name'],
        'email' => $admin['email'],
        'username' => $admin['username'],
        'password'=> $hashed_password
    ]);

    if ($executed) {
        return true;
    }

    echo $statement->errorInfo()[2];
    exit;
}

function update_admin($admin)
{
    global $db;

    $password_sent = !is_blank($admin['password']);

    $errors = validate_admin($admin, ['password_required' => $password_sent]);
    if (!empty($errors)) {
        return $errors;
    }

    $query = 'UPDATE admins SET 
                  first_name=:firstName, 
                  last_name=:lastName,
                  email=:email,
                  username=:username';

    $data = [
        'firstName' => $admin['first_name'],
        'lastName' => $admin['last_name'],
        'email' => $admin['email'],
        'username' => $admin['username'],
        'id' => $admin['id'],
    ];

    if ($password_sent) {
        $hashed_password = password_hash($admin['password'], PASSWORD_BCRYPT);
        $data['password'] = $hashed_password;

        $query .= ', hashed_password=:password';
    }

    $query .= ' WHERE id=:id LIMIT 1';

    $statement = $db->prepare($query);

    $executed = $statement->execute($data);

    if ($executed) {
        return true;
    }

    echo $statement->errorInfo()[2];
    exit;
}

function delete_admin($id)
{
    global $db;

    $statement = $db->prepare('DELETE FROM admins WHERE id=:id');

    $executed = $statement->execute([
        'id' => $id
    ]);

    if ($executed) {
        return true;
    }

    echo $statement->errorInfo()[2];
    exit;
}