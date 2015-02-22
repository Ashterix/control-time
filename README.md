Control Time
============
Time control of the script with the ability to check the intermediate points.



### How use?
##### INIT
At the beginning of your script, initiate control of time:
~~~~~~ php
use Control\ControlTime;

ControlTime::init();
~~~~~

##### ADD WAYPOINTS
In the right places in your script, add waypoints with comments:
~~~~~~ php
ControlTime::addWayPoint('Before connect to DB');
// your connect to DB
ControlTime::addWayPoint('After connect to DB');
~~~~~

##### GET RESULTS
At the end of your script, get results in the desired format:
~~~~~~ php
$resultArray = ControlTime::getResults(ControlTime::RETURN_ARRAY);
// or
$resultJSON = ControlTime::getResults(ControlTime::RETURN_JSON);
// or
$resultHTML = ControlTime::getResults(ControlTime::RETURN_HTML);
~~~~~



##### HTML demo:
~~~~~~ php
echo $resultHTML;
~~~~~
![main window](https://github.com/Ashterix/control-time/blob/master/demo.jpg)
