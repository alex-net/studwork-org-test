<?php 

namespace app\commands;

use app\models\Notes;

class NoteController extends \yii\console\Controller
{
	/**
	 * Генерация заметок ... 
	 * @param  integer $count Число заметок для генерации
	 * @return [type]         [description]
	 */
	public function actionGenerate(int $count=100)
	{
		Notes::generator($count);
	}
	/**
	 * зачистка зметок .... 
	 * @return [type] [description]
	 */
	public function actionClear()
	{
		Notes::clear();
	}

}