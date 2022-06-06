<?php 

namespace app\controllers;

use Yii;
use \app\models\Notes;

class NoteController extends \yii\web\Controller
{

    public function init()
    {
        Yii::$app->user->enableSession = false;
        parent::init();
    }
    public function behaviors()
    {
        return [
            [
                'class' => \yii\filters\auth\HttpBearerAuth::class,
                'optional' => ['index', 'view'],
            ],
            [
                'class' => \yii\filters\AccessControl::class,
                'rules' => [
                    ['allow' => true, 'actions' => ['index', 'view'], 'verbs' => ['get']],
                    ['allow' => true, 'actions' => ['create', 'update', 'drop'], 'verbs' => ['post']],
                ],
            ]
        ];
    }

    public function beforeAction($act)
    {
        $this->enableCsrfValidation=false;
        if (!parent::beforeAction($act)) {
            return false;
        }
        Yii::$app->response->format=\yii\web\Response::FORMAT_JSON;
        return true;
    }
    
    /**
     * Просмотр записей списком 
     * @return [type] [description]
     */
    public function actionIndex()
    {
        $dp = Notes::list();
        return [
            'notes' => $dp->models,
            'count' => intval($dp->totalCount),
            'pageCount' => ceil($dp->totalCount/$dp->pagination->pageSize),
            'currentPage' => ($dp->pagination->page + 1),
        ];
    }

    /**
     * загрузка по id 
     * @param  int    $id Номер запими ...
     * @return [type]     [description]
     */
    private function getNote(int $id):Notes
    {
        $n = Notes::getById($id);
        // запись не найдена или публикация не настала .. 
        if (!$n) {
            throw new \yii\web\NotFoundHttpException("Нет такой записи");
        }
        return $n;
    }

    /**
     * сохранение записи
     * @param Note  $n Объект записи ..
     */
    private function setNote(Notes &$n):array
    {
        $post = Yii::$app->request->post();
        unset($post['id']);

        foreach($n->attributes() as $key)
            if (isset($post[$key])) {
                $n->$key = $post[$key];
            }
        if ($n->save()) {
            return $n->getAttributes(['capt', 'dpubl', 'user']);
        }
        return ['errors' => $n->errors];
    }

    /**
     * модификация записи ...
     * @param int $id Номер записи для модификации :  полжительная = обнова; отрицательная = удаление 
     */
    private function modNote(int $id)
    {
        $n=$this->getNote(abs($id));
        // только авторы могут изменять заметки ... 
        if ($n->user != Yii::$app->user->id) {
            return ['error' => 'current user is not author of this note'];
        }
        // просрочено ..
        if ($n->dcr + 24 * 3600 < time()) {
            return ['error'=>'created time on note is so big'];
        } 
        // обновленпе .. 
        if ($id > 0) {
            return $this->setNote($n);
        }

        $n->kill();
        return $n->getAttributes(['capt', 'dpubl', 'user']) ;
    }
    /**
     * просмотр одиночной записи ... 
     */
    public function actionView(int $id):array
    {
        $n=$this->getNote($id);
        // закрываем записи с публикацией потом ... от остальных пользователей .. 
        if($n->dpubl > time() && Yii::$app->user->id != $n->user) {
            throw new \yii\web\NotFoundHttpException("Нет такой записи");
        }
        return $n->getAttributes(null, ['dcr', 'id']);
    }

    /**
     * создание новой записи ...
     * @return [type] [description]
     */
    public function actionCreate()
    {
        $n = new Notes;
        return $this->setNote($n);
    }

    /**
     * обновление записи ...
     * @param  int    $id Id записи ..
     * @return [type]     [description]
     */
    public function actionUpdate(int $id)
    {
        return $this->modNote($id);
    }

    /**
     * удаление записи ...
     */
    public function actionDrop(int $id)
    {
        return $this->modNote(-$id);
    }
}