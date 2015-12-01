<?php

class ArticleController extends Controller
{
    /**
     * This is the default 'index' action that is invoked
     * when an action is not explicitly requested by users.
     */
    public function actionIndex()
    {
        $dataProvider = new CActiveDataProvider('Article', array(
            'criteria'   => array(
                'condition' => "status = " . Article::STATUS_PUBLISH,
            ),
            'pagination' => array(
                'pageSize' => 100,
            ),
        ));

        $this->breadcrumbs = array(
            Yii::t('interface', 'Статьи')
        );

        $this->pageTitle = 'Site - ' . Yii::t('interface', 'Статьи');

        $this->render('index', array(
            'dataProvider' => $dataProvider,
        ));
    }

    public function actionView($slug)
    {
        $articleModel = Article::Model()->findByAttributes(array('slug' => $slug));

        if (!$articleModel) {
            Yii::app()->user->setFlash('error', 'Статья не найдена');
            $this->redirect($this->createUrl('index'));
        }

        $this->breadcrumbs = array(
            Yii::t('interface', 'Статьи') => array('index'),
            $articleModel->title
        );

        $this->pageTitle = 'Site - ' . Yii::t('interface', 'Статьи') . ", " . $articleModel->title;

        $this->render('view', array(
            'articleModel' => $articleModel,
        ));
    }
}