<?php

/**
 * This is the model class for table "article".
 *
 * The followings are the available columns in table 'article':
 * @property string $id
 * @property string $create
 * @property string $update
 * @property string $title
 * @property string $image
 * @property string $slug
 * @property string $short_text
 * @property string $text
 * @property integer $status
 */
class Article extends BaseModel
{
    const STATUS_DRAFT   = 0;
    const STATUS_PUBLISH = 1;
    const STATUS_BLOCKED = 2;

    const PATH_TO_FOLDER  = "/media/articles/";
    const PATH_TO_GALLERY = "/media/gallery/articles/";

    private $_url;

    public static function statusList($id = NULL)
    {
        $list = array(
            self::STATUS_BLOCKED => 'Не опубликован',
            self::STATUS_PUBLISH => 'Опубликован',
        );

        if ($id != NULL) {
            return $list[$id];
        }

        return $list;
    }

    /**
     * @return string the associated database table name
     */
    public function tableName()
    {
        return 'article';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            //Update
            array('update, title, short_text, text, status', 'required', 'on' => 'update'),
            array('slug', 'safe', 'on' => 'update'),
            //Search
            array('id, create, update, title, slug,  short_text, text, status, image', 'safe', 'on' => 'search'),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations()
    {
        // NOTE: you may need to adjust the relation name and the related
        // class name for the relations automatically generated below.
        return array();
    }

    /**
     * @return array customized attribute labels (name=>label)
     */
    public function attributeLabels()
    {
        return array(
            'id'         => 'ID',
            'create'     => 'Добавлен',
            'update'     => 'Редактирован',
            'title'      => 'Заголовок',
            'image'      => 'Картинка',
            'slug'       => 'Слаг',
            'short_text' => 'Аннотация',
            'text'       => 'Статья',
            'status'     => 'Статус',
        );
    }

    /**
     * Retrieves a list of models based on the current search/filter conditions.
     *
     * Typical usecase:
     * - Initialize the model fields with values from filter form.
     * - Execute this method to get CActiveDataProvider instance which will filter
     * models according to data in model fields.
     * - Pass data provider to CGridView, CListView or any similar widget.
     *
     * @return CActiveDataProvider the data provider that can return the models
     * based on the search/filter conditions.
     */
    public function search()
    {
        // @todo Please modify the following code to remove attributes that should not be searched.

        $criteria = new CDbCriteria;

        $criteria->compare('t.id', $this->id, true);
        $criteria->compare('t.create', $this->create, true);
        $criteria->compare('t.update', $this->update, true);
        $criteria->compare('t.title', $this->title, true);
        $criteria->compare('t.image', $this->image, true);
        $criteria->compare('t.slug', $this->slug, true);
        $criteria->compare('t.short_text', $this->short_text, true);
        $criteria->compare('t.text', $this->text, true);
        $criteria->compare('t.status', $this->status);

        return new CActiveDataProvider($this, array(
            'criteria' => $this->ml->modifySearchCriteria($criteria),
        ));
    }

    /**
     * Returns the static model of the specified AR class.
     * Please note that you should have this exact method in all your CActiveRecord descendants!
     * @param string $className active record class name.
     * @return Article the static model class
     */
    public static function model($className = __CLASS__)
    {
        return parent::model($className);
    }

    public function behaviors()
    {
        return array(
            'ml' => array(
                'class'               => 'application.models.behaviors.MultilingualBehavior',
                'localizedAttributes' => array('title', 'short_text', 'text'),
                'langClassName'       => 'ArticleLang',
                'langTableName'       => '{{article_lang}}',
                'languages'           => $this->getTranslatedLanguages(),
                'defaultLanguage'     => Yii::app()->params['defaultLanguage'],
                'langForeignKey'      => 'article_id',
                'dynamicLangClass'    => true,
            ),
        );
    }

    public function scopes()
    {
        return array(
            'published' => array(
                'condition' => 't.status = ' . self::STATUS_PUBLISH,
            ),
        );
    }

    public function defaultScope()
    {
        return $this->ml->localizedCriteria();
    }

    public static function getSelectedData()
    {
        $criteria            = new CDbCriteria;
        $criteria->condition = 'status <> ' . self::STATUS_DRAFT;

        $result = self::Model()->findAll($criteria);
        $result = $result ? CHtml::listData($result, 'id', 'title') : array();
        return $result;
    }

    public static function getTitul($id)
    {
        $result = self::Model()->findByPk($id);
        return $result->title;
    }

    public function getUrl()
    {
        if ($this->_url === null)
            $this->_url = DMultilangHelper::processLangInUrl(Yii::app()->createUrl('article/view', array('slug' => $this->slug)));
        return $this->_url;
    }

    public static function deleteInsideImages($id)
    {
        $images = Yii::app()->db->CreateCommand()
            ->select('link')
            ->from('article_image_map')
            ->where('article_id = ' . intval($id))
            ->queryColumn();

        if (!$images) {
            return false;
        }

        $webroot = Yii::getPathOfAlias('webroot');

        foreach ($images as $img) {
            if (is_file($webroot . $img)) {
                unlink($webroot . $img);
            }
        }

        $sql = "DELETE FROM `article_image_map` WHERE article_id = '" . $id . "'";
        Yii::app()->db->createCommand($sql)->execute();
        return true;
    }

    public static function fullDeleteArticle($id)
    {
        $model = self::Model()->findByPk(intval($id));
        if (!$model) {
            return false;
        }

        if (!empty($model->image) && is_file(Yii::getPathOfAlias('webroot') . $model->image)) {
            unlink(Yii::getPathOfAlias('webroot') . $model->image);
        }

        self::deleteInsideImages($id);

        $gallery = Gallery::getGalleryId(Gallery::TYPE_ARTICLE, $model->id);

        if ($gallery) {
            Gallery::deleteGallery($gallery);
        }
        $model->delete();
        return true;
    }
}
