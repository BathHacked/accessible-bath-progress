<?php


namespace BathHacked;


use Monolog\Handler\SlackWebhookHandler;

class Helpers
{
    public static function indexOn($array, $key) {
        return array_reduce($array, function($c, $v) use ($key)	{
            $ckey = is_object($v) ? $v->$key : $v[$key];
            $c[$ckey] = $v;
            return $c;
        }, []);
    }

    public static function pluck($array, $key, $index = null)
    {
        return array_reduce($array, function($c, $v) use ($key, $index)	{
            if(!empty($index))
            {
                $ckey = is_object($v) ? $v->$index : $v[$index];
                $c[$ckey] = is_object($v) ? $v->$key : $v[$key];
            }
            else
            {
                $c[] = is_object($v) ? $v->$key : $v[$key];
            }
            return $c;
        }, []);
    }

    public static function logger()
    {
        $settings = include __DIR__ . '/../../config/logger.php';
        $logger = new \Monolog\Logger($settings['name']);
        $logger->pushProcessor(new \Monolog\Processor\UidProcessor());
        $logger->pushHandler(new \Monolog\Handler\StreamHandler($settings['path'], $settings['level']));
        if(isset($settings['slack']))
        {
            $handler = new SlackWebhookHandler(
                $settings['slack'], null, null, true,
                null, false, true,
                \Monolog\Logger::INFO, true, []);
            $logger->pushHandler($handler);
        }
        return $logger;
    }
}