<?php

require_once __DIR__.'/../lib/Silex/autoload.php';

$app = new Silex\Application();
$app['debug'] = TRUE;

/*                                                                            */
/*                          Registering Extensions                            */
/*                                                                            */
 
/* Doctrine Extension DBAL. ORM isn't supplied. */
$app->register(new Silex\Extension\DoctrineExtension(), array(
    'db.options' => array(
        'driver'    => 'pdo_sqlite',
        'path'      => __DIR__.'/../app.sqlite',
    ),
    'db.dbal.class_path'    => __DIR__.'/vendor/doctrine-dbal/lib',
    'db.common.class_path'  => __DIR__.'/vendor/doctrine-common/lib',
));

/* Twig Extension. */
$app->register(new Silex\Extension\TwigExtension(), array(
    'twig.path'       => __DIR__.'/../views',
    'twig.class_path' => __DIR__.'/vendor/twig/lib',
));

/* Gravatar Extension. */
$app->register(new Redpanda\Gravatar\Extension(), array(
    'gravatar.options' => array(
        'size'    => 100,
        'rating'   => 'g',
        'default' => 'mm',
    )
));

$app['twig']->addFilter('nl2br', new Twig_Filter_Function('nl2br', array('is_safe' => array('html'))));
$app['twig']->addFilter('number', new Twig_Filter_Function('number', array('is_safe' => array('html'))));


$app->get('/', function() use($app) {
    $sql = 'SELECT id FROM editions ORDER BY date DESC LIMIT 1';
    $edition = $app['db']->fetchAssoc($sql);  
    if(!$edition) die;
    return $app->redirect('/edition/'.$edition['id']);
});


$app->get('/edition/{id}', function($id) use($app) {
    
    $sql = 'SELECT * FROM editions WHERE id = ?';
    $currentEdition = $app['db']->fetchAssoc($sql, array($id));
    
    if(!$currentEdition)
        return $app->redirect('/');
    
    $now = new DateTime();
    $currentEditionDate = new DateTime($currentEdition['date']);
    
    /* Getting all talks in an edition */
    $sql = 'SELECT * FROM talks WHERE edition = ? ORDER BY time ASC';
    $talks = $app['db']->fetchAll($sql, array($id));
    
    /* Getting all editions for sidebar menu */
    $sql = 'SELECT id, date FROM editions ORDER BY date DESC';
    $editions = $app['db']->fetchAll($sql);
    
    /* Getting all edition sponsors for sidebar menu */
    $sql = 'SELECT * FROM sponsors s 
            JOIN sponsors_editions se ON s.id = se.sponsor
            WHERE se.edition = ?';
    $sponsors = $app['db']->fetchAll($sql, array($id));
    
    /* Getting images for slideshow */
    $slideshow = array();
    foreach(glob(dirname(__FILE__).'/static/slideshow/*.jpg') as $image)
        $slideshow[] = str_replace(dirname(__FILE__), '', $image);
    shuffle($slideshow);
    
    /* Permalink is for Facebook Comments */
    $permalink = sprintf('http://%s/edition/%d', $_SERVER['HTTP_HOST'], $id);
    
    return $app['twig']->render('index.twig', array(
        'current' => $currentEdition,
        'editions' => $editions,
        'talks' => $talks,
        'sponsors' => $sponsors,
        'countdown' => $now->diff($currentEditionDate)->format('%R%a'),
        'permalink' => $permalink,
        'slideshow' => $slideshow
    ));
});


$app->get('/talk/{id}', function($id) use($app) {
    $sql = 'SELECT * FROM talks WHERE id = ?';
    $talk = $app['db']->fetchAssoc($sql, array($id));
    return $app['twig']->render('talk.twig', array('talk' => $talk));
});


$app->error(function (\Exception $e, $code) use($app) {
    return $app->redirect('/');
});

$app->run();

