<?php

namespace Mini\Model;

use PDO;

class Users
{
    /**
     * The database connection
     * @var PDO
     */
    private $db;

    /**
     * @var orderUsersByThisFieldFirst
     * @var orderUsersByThisFieldSecond
     */
    private $orderUsersByThisFieldFirst = 'lastname';
    private $orderUsersByThisFieldSecond = 'firstname';
    /**
     * When creating the model, the configs for database connection creation are needed
     * @param $config
     */
    function __construct($config)
    {
        // PDO db connection statement preparation
        $dsn = 'mysql:host=' . $config['db_host'] . ';dbname=' . $config['db_name'] . ';port=' . $config['db_port'];

        // note the PDO::FETCH_OBJ, returning object ($result->id) instead of array ($result["id"])
        // @see http://php.net/manual/de/pdo.construct.php
        $options = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING);

        // create new PDO db connection
        $this->db = new PDO($dsn, $config['db_user'], $config['db_pass'], $options);
    }

    function getAllUsers(){
        $sql = "
            SELECT user_id, firstname, lastname, preferredname, month, day, year, phone1, phone2, email1, email2, created, updated
            FROM users

        ";
        $query = $this->db->prepare($sql);
        $query->execute();

        $users = $query->fetchAll();
        usort($users, array($this, 'sortUsers'));

        return $users;
    }

    function sortUsers($a, $b){
        $field = $this->orderUsersByThisFieldFirst;
        $field2 = $this->orderUsersByThisFieldSecond;

        $primary = strcmp($a->$field, $b->$field);
        $secondary = strcmp($a->$field2, $b->$field2);

        if (! $primary){
            return $secondary;
        }else{
            return $primary;
        }
    }

    function getUser($user_id){
        $sql = "SELECT user_id, firstname, lastname, preferredname, month, day, year, phone1, phone2, email1, email2, created, updated FROM users WHERE (user_id = :user_id)";
        $query = $this->db->prepare($sql);
        $params = array(':user_id'=>$user_id);
        $query->execute($params);
        return $query->fetch();
    }

    function updateUser($user_id, $firstname, $lastname, $preferrredname, $month, $day, $year, $gender, $phone1, $phone2, $email1, $email2){

        $sql = "UPDATE users
                SET
                  firstname = :firstname,
                  lastname = :lastname,
                  preferredname = :preferredname,
                  month = :month,
                  day = :day,
                  year = :year,
                  gender = :gender,
                  phone1 = :phone1,
                  phone2 = :phone2,
                  email1 = :email1,
                  email2 = :email2
                WHERE (user_id = :user_id)";
        $query = $this->db->prepare($sql);
        $params = array(
            ':user_id'=>$user_id,
            ':firstname'=>$firstname,
            ':lastname'=>$lastname,
            ':preferredname'=>$preferrredname,
            ':month' => $month,
            ':day'=>$day,
            ':year'=>$year,
            ':gender'=>$gender,
            ':phone1'=>$phone1,
            ':phone2'=>$phone2,
            ':email1'=>$email1,
            ':email2'=>$email2
        );
        $query->execute($params);
    }

    function addUser($firstname, $lastname, $preferredname, $month, $day, $year, $phone1, $phone2, $email1, $email2){
        $sql = "INSERT INTO users (firstname, lastname, preferredname, month, day, year, phone1, phone2, email1, email2, created)
                VALUES            (:firstname, :lastname, :preferredname, :month, :day, :year, :phone1, :phone2, :email1, :email2, NOW())";
        $query = $this->db->prepare($sql);
        $params = array(
            ':firstname' => $firstname,
            ':lastname' => $lastname,
            ':preferredname' => $preferredname,
            ':month' => $month,
            ':day' => $day,
            ':year'=> $year,
            ':phone1' => $phone1,
            ':phone2' => $phone2,
            ':email1' => $email1,
            ':email2' => $email2
        );
        $query->execute($params);

    }

    function deleteUser($user_id){
        $sql = "DELETE FROM users WHERE user_id = :user_id";
        $query = $this->db->prepare($sql);
        $params = array(':user_id'=>$user_id);
        $query->execute($params);
    }

    function searchUser($searchTerm){

        $sql = "
          SELECT user_id, firstname, lastname, preferredname, month, day, year, phone1, phone2, email1, email2
          FROM users
          WHERE (firstname LIKE :searchTerm )
          OR (lastname LIKE :searchTerm)
          OR (preferredname LIKE :searchTerm)
          OR (month LIKE :searchTerm)
          OR (month LIKE :searchTerm_converted)
          OR (day LIKE :searchTerm)
          OR (year LIKE :searchTerm)
          OR (phone1 LIKE :searchTerm)
          OR (phone2 LIKE :searchTerm)
          OR (email1 LIKE :searchTerm)
          or (email2 LIKE :searchTerm)";
        $query = $this->db->prepare($sql);
        $params = array(
            ':searchTerm'=>'%' . $searchTerm . '%',
            ':searchTerm_converted'=>'NotAValidDate');

        //this next section checks the string entered for a name of a month, converts the string to MM format if so
        if($this->checkForMonth($searchTerm)){
            $params[':searchTerm_converted']='%' . $this->checkForMonth($searchTerm) . '%';
        }

        $query->execute($params);

        $users = $query->fetchAll();
        usort($users, array($this, 'sortUsers'));

        return $users;
    }

    function checkForMonth($searchTerm){
        $searchTerm = strtolower($searchTerm);
        $months = array(
            'january'=>'01',
            'february'=>'02',
            'march'=>'03',
            'april'=>'04',
            'may'=>'05',
            'june'=>'06',
            'july'=>'07',
            'august'=>'08',
            'septemnber'=>'09',
            'october'=>'10',
            'november'=>'11',
            'december'=>'12',
            'jan'=>'01',
            'feb'=>'02',
            'mar'=>'03',
            'apr'=>'04',
            'may'=>'05',
            'jun'=>'06',
            'jul'=>'07',
            'aug'=>'08',
            'sep'=>'09',
            'oct'=>'10',
            'nov'=>'11',
            'dec'=>'12'
        );
        if(array_key_exists($searchTerm,$months)){
            return $months[$searchTerm];
        }else{
            return false;
        }
    }
}