<?php
/**
 * ControlTime
 * @version: 1.0.0
 *
 * @file: ControlTime.php
 * @author Ashterix <ashterix69@gmail.com>
 *  
 * Class - ControlTime
 * @description Time control of the script with the ability to check the intermediate points.
 *
 * Created by JetBrains PhpStorm.
 * Date: 22.02.15
 * Time: 2:32
 */

namespace Control;


class ControlTime {

    const RETURN_ARRAY  = 1;
    const RETURN_HTML   = 2;
    const RETURN_JSON   = 3;

    private static $wayPoints   = [];

    private static $timeStart   = 0;
    private static $timePrev    = 0;

    private static $stopControl = false;

    private static $returnMethods = [
        self::RETURN_ARRAY  => 'returnArray',
        self::RETURN_HTML   => 'returnHTML',
        self::RETURN_JSON   => 'returnJSON',
    ];


    /**
     * function init()
     * @description initiated control time
     *
     * @throws \Exception
     */
    public static function init()
    {
        if (self::$timeStart > 0){
            throw new \Exception("Class " . self::className() ." has already been initiated in " . self::$wayPoints[0]['file'] . " line: " . self::$wayPoints[0]['line']);
        }

        self::addWayPoint(self::className() . ' init');
    }

    /**
     * function addWayPoint()
     * @description add way point to list
     *
     * @param string $comment
     * @throws \Exception
     */
    public static function addWayPoint($comment = '')
    {
        if (self::$stopControl){
            $wayPoints = self::$wayPoints;
            throw new \Exception("Class " . self::className() .": it is impossible track the time. The result has been received " . end($wayPoints)['file'] . " line: " . end($wayPoints)['line']);
        }

        list($usec, $sec) = explode(" ", microtime());
        $curTime = (float)$sec + (float)$usec;

        if (self::$timeStart == 0){
            self::$timeStart = $curTime;
        }

        if (self::$timePrev == 0){
            self::$timePrev = $curTime;
        }

        $backtrace = debug_backtrace();
        $backtrace = end($backtrace);

        self::$wayPoints['total_time'] = round(($curTime - self::$timeStart), 3) * 1000;

        self::$wayPoints[] = [
            "comment"   => (!empty($comment)) ? $comment : "untitled",
            "file"      => $backtrace['file'],
            "line"      => $backtrace['line'],
            "time"      => $curTime,
            "from_prev" => round(($curTime - self::$timePrev), 3) * 1000,
            "from_start"=> round(($curTime - self::$timeStart), 3) * 1000
        ];
        self::$timePrev = $curTime;
    }

    /**
     * function getResults()
     * @description get results of control time
     *
     * @param int $typeReturn
     * @throws \Exception
     * @return mixed
     */
    public static function getResults($typeReturn = self::RETURN_ARRAY)
    {
        if (!self::$stopControl){
            self::addWayPoint(self::className() . ' finish');
            self::$stopControl = true;
        }

        return self::setReturnMethod($typeReturn, self::$wayPoints);
    }

    /**
     * function setReturnMethod()
     * @description Call method for return results by type
     *
     * @param $type
     * @param $args
     * @return mixed
     * @throws \Exception
     */
    private static function setReturnMethod($type, $args)
    {
        if (!isset(self::$returnMethods[$type])) {
            throw new \Exception("Class " . self::className() .": not implement handler for type return with $type");
        }

        $method = self::$returnMethods[$type];
        return self::$method($args);
    }

    /**
     * function returnArray()
     * @description return results as array
     *
     * @param $array
     * @return array
     */
    private static function returnArray($array)
    {
        return $array;
    }

    /**
     * function returnJSON()
     * @description return results as JSON
     *
     * @param $array
     * @return string
     */
    private static function returnJSON($array)
    {
        return json_encode($array);
    }

    private static function returnHTML($array)
    {
        ob_start();
        ?>
        <style>
            .table_log{
                background: #fbfbfb;
                width: 100%;
                margin: 30px auto;
                border-radius: 10px;
                font-size: 16px;
            }
            .table_log tr:first-of-type th{
                border-radius: 10px 10px 0 0 ;
            }
            .table_log tr:last-of-type th{
                border-radius: 0 0 10px 10px ;
            }
            .table_log th{
                background: #797979;
                color: #FFF;
                text-shadow: -1px -1px 0 #272727;
                padding: 5px 10px;
                font-weight: bold;
                text-align: center;
                vertical-align: middle
            }
            .table_log td{
                background: #e4e4e4;
                font-weight: normal;
                padding: 3px 20px;
                font-size: 14px;
                color: #555;
            }
            .table_log tr:hover td{
                background: #d2d385;
                cursor: default;
            }
            .table_log .total_time{
                text-align: left;
                padding-left: 20px;
            }
            .table_log td.time_td{
                width: 100px;
                text-align: center;
                padding: 0;
            }
        </style>
        <table class="table_log">
            <tr>
                <th colspan="4">
                    <?=self::className()?> <br>for<br>
                    http://<?=$_SERVER['SERVER_NAME']?><?=$_SERVER['REQUEST_URI']?>
                </th>
            </tr>
            <tr>
                <th>Comment</th>
                <th>Time from prev (ms)</th>
                <th>Time from start (ms)</th>
                <th>File/Line</th>
            </tr>
            <?
            $totalTime = $array['total_time'];
            unset($array['total_time']);
            foreach($array as $log){
                ?>
                <tr>
                    <td><?= $log['comment']; ?></td>
                    <td class="time_td"><?= $log['from_prev']; ?></td>
                    <td class="time_td"><?= $log['from_start']; ?></td>
                    <td><?= $log['file']; ?> <b>line:<?= $log['line']; ?></b></td>
                </tr>
            <?
            }
            ?>
            <tr>
                <th class="total_time" colspan="4">Total time: <?= $totalTime; ?> ms</th>
            </tr>
        </table>
        <?
        $html=ob_get_contents();
        ob_end_clean();
        return $html;
    }

    /**
     * function className()
     * @description get class name without namespaces
     *
     * @return string
     */
    private static function className()
    {
        $classNames = explode("\\", __CLASS__);
        return end($classNames);
    }

}