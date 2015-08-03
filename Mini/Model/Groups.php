<?php

namespace Mini\Model;

use PDO;

class Groups
{
    /**
     * The database connection
     * @var PDO
     */
    private $db;

    /**
     * This is where we set the field by which to sort the groups in views where there are multiple groups.
     * @var orderGroupByThisField
     */
    private $orderGroupByThisField = 'group_name';

    /**
     * When creating the model, the configs for database connection creation are needed
     * @param $config
     */
    function __construct($config)
    {
        // PDO db connection statement preparation
        $dsn = 'mysql:host=' . $config['db_host'] . ';dbname='    . $config['db_name'] . ';port=' . $config['db_port'];

        // note the PDO::FETCH_OBJ, returning object ($result->id) instead of array ($result["id"])
        // @see http://php.net/manual/de/pdo.construct.php
        $options = array(PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING);

        // create new PDO db connection
        $this->db = new PDO($dsn, $config['db_user'], $config['db_pass'], $options);
    }

    function addGroup($values){
        $sql = "INSERT INTO groups (type, group_name, address1, address2, city, state, zipcode, created)
                VALUES            (:type, :group_name, :address1, :address2, :city, :state, :zipcode, NOW())";
        $query = $this->db->prepare($sql);
        $params = array(
            ':type' => $values['type'],
            ':group_name'=>$values['group_name'],
            ':address1'=>$values['address1'],
            ':address2'=>$values['address2'],
            ':city'=>$values['city'],
            ':state'=>$values['state'],
            ':zipcode'=>$values['zipcode']
        );
        $query->execute($params);
    }

    function updateGroup($values){
        $sql = "UPDATE groups
                SET
                  type = :type,
                  group_name = :group_name,
                  address1 = :address1,
                  address2 = :address2,
                  city = :city,
                  state = :state,
                  zipcode = :zipcode
                WHERE group_id = :group_id ";
        $query = $this->db->prepare($sql);
        $params = array(
            ':type' => $values['type'],
            ':group_name'=>$values['group_name'],
            ':address1'=>$values['address1'],
            ':address2'=>$values['address2'],
            ':city'=>$values['city'],
            ':state'=>$values['state'],
            ':zipcode'=>$values['zipcode'],
            ':group_id' =>$values['group_id']
        );

        $query->execute($params);
    }

    function deleteGroup($group_id){
        $sql = "
            DELETE
            FROM groups
            WHERE group_id = :group_id
        ";
        $query = $this->db->prepare($sql);
        $params = array(':group_id'=>$group_id);

        $query->execute($params);
    }

    function assignUserToGroup($user_id, $group_id){
        $sql = "
            INSERT INTO assignments (user_id, group_id)
            VALUES (:user_id, :group_id)
        ";
        $query = $this->db->prepare($sql);
        $params = array(
            ':user_id' => $user_id,
            ':group_id' => $group_id
        );
        $query->execute($params);
    }

    function getAllGroups($type = 'group'){
        $sql = "
            SELECT group_id, type, group_name, address1, address2, city, state, zipcode
            FROM groups
            WHERE type = :type
        ";
        $params = array(':type'=>$type);
        $query = $this->db->prepare($sql);

        $query->execute($params);
        $groups = $query->fetchAll();
        usort($groups, array($this, "sortGroups"));
        return $groups;
    }

    function sortGroups($a, $b){
        $field = $this->orderGroupByThisField;
        return strcmp($a->$field, $b->$field);
    }

    function getGroup($group_id){
        $sql = "
            SELECT group_id, type, group_name, address1, address2, city, state, zipcode
            FROM groups
            WHERE group_id = :group_id
        ";
        $query = $this->db->prepare($sql);
        $params = array(':group_id' => $group_id);

        $query->execute($params);

        return $query->fetch();
    }

    function getUsersByGroup($group_id){
        $sql = "
            SELECT
                users.user_id AS user_id,
                users.firstname AS firstname,
                users.lastname AS lastname,
                users.preferredname AS preferredname,
                users.month AS month,
                users.day AS day,
                users.year AS year,
                users.phone1 AS phone1,
                users.phone2 AS phone2,
                users.email1 AS email1,
                users.email2 AS email2
            FROM users INNER JOIN assignments
            ON users.user_id = assignments.user_id
            WHERE assignments.group_id = :group_id
        ";
        $query = $this->db->prepare($sql);
        $params = array(':group_id'=>$group_id);

        $query->execute($params);

        return $query->fetchAll();
    }

    function getGroupsByUser($user_id){
        $sql = "
            SELECT
                groups.group_id AS group_id,
                groups.type AS type,
                group.group_name AS group_name,
                groups.address1 AS address1,
                groups.address2 AS address2,
                groups.city AS city,
                groups.zipcode AS zipcode
            FROM groups INNER JOIN assignments
            ON groups.group_id = assignments.group_id
            WHERE assignments.user_id = :user_id
        ";
        $query = $this->db->prepare($sql);
        $params = array(':user_id'=>$user_id);
        $query->execute($params);

        return $query->fetchAll();
    }


}