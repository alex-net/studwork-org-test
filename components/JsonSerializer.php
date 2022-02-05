<?php 

namespace app\components;

class JsonSerializer extends \yii\rest\Serializer
{
	public function serialize($data)
	{
		return json_encode($data);
	}
}