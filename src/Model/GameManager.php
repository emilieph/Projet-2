<?php

/**
 * Created by PhpStorm.
 * User: sylvain
 * Date: 07/03/18
 * Time: 18:20
 * PHP version 7
 */

namespace App\Model;

/**
 * Class GameManager
 *
 */
class GameManager extends AbstractManager
{
    /**
     *
     */
    private const TABLE = 'game';

    /**
     *  Initializes this class.
     */
    public function __construct()
    {
        parent::__construct(self::TABLE);
    }

    /**
     * Insert into table the character data.
     * @param array $character
     * @return int
     */
    public function newGame(array $character): int
    {
        $statement = $this->pdo->prepare("INSERT INTO " . self::TABLE . " (name, image, strength, energy, humor, agility, max_floor, event_count, save, is_ended, user_id) VALUES (:name, :image, :strength, :energy, :humor, :agility, 1, 0, :save, FALSE, 1)");
        $statement->bindValue(':name', $character['name'], \PDO::PARAM_STR);
        $statement->bindValue(':image', $character['image'], \PDO::PARAM_STR);
        $statement->bindValue(':strength', $character['strength'], \PDO::PARAM_INT);
        $statement->bindValue(':energy', $character['energy'], \PDO::PARAM_INT);
        $statement->bindValue(':humor', $character['humor'], \PDO::PARAM_INT);
        $statement->bindValue(':agility', $character['agility'], \PDO::PARAM_INT);
        $statement->bindValue(':save', date("Y-m-d H:i:s"), \PDO::PARAM_STR);

        if ($statement->execute()) {
            return (int)$this->pdo->lastInsertId();
        }
    }

    public function save(int $id)
    {
        $statement = $this->pdo->prepare("UPDATE " . self::TABLE . " SET save = :save WHERE id=:id");
        $statement->bindValue('id', $id, \PDO::PARAM_INT);
        $statement->bindValue(':save', date("Y-m-d H:i:s"), \PDO::PARAM_STR);
        $statement->execute();
    }

    public function  levelUp(array $update): int
    {
        $statement = $this->pdo->prepare ( "UPDATE " . self::TABLE . " SET strength = strength + :strength, energy = energy + :energy, humor = humor + :humor, agility = agility + :agility, max_floor = :max_floor, save = :save WHERE id=:id" );
        $statement->bindValue ( 'id', $update['id'], \PDO::PARAM_INT );
        $statement->bindValue ( ':strength', $update['strength'], \PDO::PARAM_INT );
        $statement->bindValue ( ':energy', $update['energy'], \PDO::PARAM_INT );
        $statement->bindValue ( ':humor', $update['humor'], \PDO::PARAM_INT );
        $statement->bindValue ( ':agility', $update['agility'], \PDO::PARAM_INT );
        $statement->bindValue ( ':max_floor', ($update['max_floor'] + 1), \PDO::PARAM_INT );
        $statement->bindValue ( ':save', date ( "Y-m-d H:i:s" ), \PDO::PARAM_STR );

        if ($statement->execute()) {
            return $update['id'];
        }
    }
        
    public function isEnded($id)
    {
        $sql = 'SELECT is_ended FROM game WHERE id = :id;';
        $statement = $this->pdo->prepare($sql);
        $statement->bindValue(':id', $id, \PDO::PARAM_INT);
    }

    public function killPlayer($idGame)
    {
        //UPDATE game SET is_ended = 0 WHERE id = 3;
         $this->pdo->exec("UPDATE " . self::TABLE . " SET is_ended = 1 WHERE id = " . $idGame);
    }

    public function changeFloor($idGame, $floor)
    {
        $this->pdo->exec("UPDATE " . self::TABLE . " SET max_floor = " . $floor . " WHERE id = " . $idGame);
    }

    public function updatePlayerEvent($countEvent, $idGame)
    {
        $this->pdo->exec("UPDATE " . self::TABLE . " SET event_count = " . $countEvent . " WHERE id = " . $idGame);
    }
    
    public function countPlayerEvents($idGame)
    {
        //select count(*) as events from event e inner join  game_has_event ge on ge.event_id = e.id
        //where  e.floor_restriction = 1 and ge.game_id = 3;
        $query = "SELECT event_count FROM " .  self::TABLE;
        $query .= " WHERE id = :idGame";
        $statement = $this->pdo->prepare($query);
        $statement->bindValue(':idGame', $idGame, \PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetch();
    }
}
