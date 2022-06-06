<?php 

namespace app\models;

use Yii;
use \app\models\User;

class Notes extends \yii\base\Model
{
    public $id, $dcr, $dpubl, $user;
    public $capt, $body;

    public function rules()
    {
        return [
            [['id', 'dcr', 'dpubl'], 'integer'],
            ['dcr', 'default', 'value' => time()],
            ['user',  'default', 'value' => Yii::$app->user->id,],
            [['capt', 'body'], 'string'],
            [['capt', 'body'], ' trim'],
            ['user', 'in','range' => User::getUserList()],
            [['user', 'dcr', 'dpubl', 'capt', 'body'], 'required'],
        ];
    }

    /**
     * генерация новых псевдоэлементов ... 
     * @param  integer $co Число элементов .. 
     * @return [type]      [description]
     */
    public static function generator(int $co = 100)
    {
        if ($co < 1) {
            return;
        }

        $users = User::getUserList();
        $data = [];
        for($i = 0; $i < $co; $i++) {
            $data[] = [
                'dcr' => rand(strtotime('-1 week'), strtotime('+1 week')),
                'dpubl' => rand(strtotime('-1 week'), strtotime('+1 week')),
                'capt' => Yii::$app->security->generateRandomString(10),
                'body' => Yii::$app->security->generateRandomString(50),
                'user' => rand(min($users), max($users)),
            ];
        }

        Yii::$app->db->createCommand()->batchInsert('{{%notes}}', array_keys($data[0]), $data)->execute();
    }

    /**
     * очистка таблицы ... 
     * @return [type] [description]
     */
    public static function clear()
    {
        Yii::$app->db->createCommand()->delete('{{%notes}}')->execute();
        Yii::$app->db->createCommand('vacuum')->execute();
    }

    public static function list()
    {
        $q = new \yii\db\Query;
        $q->from('{{%notes}}');
        $q->select(['id', 'capt', 'dpubl', 'user']);
        $publ = ['>', 'dpubl', time()];
        // есть авторезация .... 
        if (Yii::$app->user->id) {
            // добавляем все заметки текущего юзера ..
            $publ = ['or', $publ, ['=', 'user', Yii::$app->user->id]];
        }
        
        $where = ['and', $publ];
        $q->where($where);
        $cmd = $q->createCommand();

        return new \yii\data\SqlDataProvider([
            'sql' => $cmd->sql,
            'params' => $cmd->params,
            'pagination' => [
                'pageSize' => 5,
                'pageParam' => 'p',
            ],
            'sort' => [
                'attributes' => ['dpubl', 'dcr'],
                'defaultOrder' => [
                    'dpubl' => SORT_DESC,
                    'dcr' => SORT_ASC
                ],
            ],
        ]);
    }

    /**
     * достать запись ... 
     * @param  int    $id Номер записи 
     * @return [type]     [description]
     */
    public static function getById(int $id)
    {
        $res = Yii::$app->db->createCommand('select * from {{%notes}} where id=:id limit 1', [':id' => $id])->queryOne();
        if(!$res) {
            return ;
        }
        foreach(['id', 'dcr', 'dpubl', 'user'] as $k) {
            $res[$k] = intval($res[$k]);
        }
        return new static($res);
    }

    /**
     * сохранение даных 
     * @return [type] [description]
     */
    public function save():bool
    {
        if (!$this->validate()) {
            return false;
        }

        $data=$this->getAttributes(null, ['id']);
        if ($this->id) {
            Yii::$app->db->createCommand()->update('{{%notes}}', $data, ['id' => $this->id])->execute();
        } else {
            Yii::$app->db->createCommand()->insert('{{%notes}}', $data)->execute();
            $this->id = intval(Yii::$app->db->lastInsertID);
        }

        return true;
    }

    /**
     * удаление ... 
     * @return [type] [description]
     */
    public function kill(): bool
    {
        if (!$this->id) {
            return false;
        }
        Yii::$app->db->createCommand()->delete('{{%notes}}', ['id' => $this->id])->execute();
        return true;
    }
}