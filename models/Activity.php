<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "activity".
 *
 * @property int $id
 * @property string $title
 * @property string $body
 * @property int $start_date
 * @property int $end_date
 * @property int $created_at
 * @property int $updated_at
 * @property int $author_id
 * @property boolean $cycle
 * @property boolean $main
 * @property User $author
 * @property Calendar[] $calendarRecords
 * @property User[] $users
 */


class Activity extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'activity';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['body', 'start_date', 'end_date', 'title', 'author_id'], 'required'],
            [['cycle', 'main'], 'boolean'],
            [['author_id'], 'integer'],
            [['end_date'], 'checkEndDate'],
            [['start_date', 'end_date'], 'date', 'format' => 'php:d.m.Y'],
            [['created_at', 'updated_at'], 'date', 'format' => 'php:d.m.Y'],
            [['title', 'body'], 'string', 'max' => 255],
        ];
    }

    public function beforeValidate()
    {
        $this->author_id = Yii::$app->user->identity->id;

        return parent::beforeValidate();
    }

    public function checkEndDate($attr, $value)
    {
        $startDateTimestamp = Yii::$app->formatter->asTimestamp($this->start_date);
        $endDateTimestamp = Yii::$app->formatter->asTimestamp($this->end_date);

        if ($startDateTimestamp > $endDateTimestamp) {
            $this->addError($attr, 'Дата конца события, не может быть больше даты начала');
        }
    }

    public function behaviors()
    {
        return [
            'timestampBehavior' => [
                'class' => \yii\behaviors\TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                    'value' => time(),
                ],
            ],
        ];
    }

    public function beforeSave($insert)
    {
        $this->start_date = Yii::$app->formatter->asTimestamp($this->start_date);

        if (!isset($this->end_date)) {
            $this->end_date = $this->start_date;
        } else {
            $this->end_date = Yii::$app->formatter->asTimestamp($this->end_date);

        }
        return parent::beforeSave($insert); // TODO: Change the autogenerated stub
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'body' => 'Body',
            'start_date' => 'Start Date',
            'end_date' => 'End Date',
            'author_id' => 'User ID',
            'cycle' => 'Cycle',
            'main' => 'Main',
        ];
    }


    public function getUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->via('calendarRecords');
    }


    public function getCalendarRecords()
    {
        return $this->hasMany(Calendar::class, ['activity_id' => 'id']);
    }

    public function getAuthor()
    {
        return $this->hasOne(User::class, ['id' => 'author_id']);
    }
}
