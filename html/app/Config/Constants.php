<?php

//--------------------------------------------------------------------
// App Namespace
//--------------------------------------------------------------------
// This defines the default Namespace that is used throughout
// CodeIgniter to refer to the Application directory. Change
// this constant to change the namespace that all application
// classes should use.
//
// NOTE: changing this will require manually modifying the
// existing namespaces of App\* namespaced-classes.
//
defined('APP_NAMESPACE') || define('APP_NAMESPACE', 'App');

/*
|--------------------------------------------------------------------------
| Composer Path
|--------------------------------------------------------------------------
|
| The path that Composer's autoload file is expected to live. By default,
| the vendor folder is in the Root directory, but you can customize that here.
*/
defined('COMPOSER_PATH') || define('COMPOSER_PATH', ROOTPATH . 'vendor/autoload.php');

/*
|--------------------------------------------------------------------------
| Timing Constants
|--------------------------------------------------------------------------
|
| Provide simple ways to work with the myriad of PHP functions that
| require information to be in seconds.
*/
defined('SECOND') || define('SECOND', 1);
defined('MINUTE') || define('MINUTE', 60);
defined('HOUR')   || define('HOUR', 3600);
defined('DAY')    || define('DAY', 86400);
defined('WEEK')   || define('WEEK', 604800);
defined('MONTH')  || define('MONTH', 2592000);
defined('YEAR')   || define('YEAR', 31536000);
defined('DECADE') || define('DECADE', 315360000);

/*
|--------------------------------------------------------------------------
| Exit Status Codes
|--------------------------------------------------------------------------
|
| Used to indicate the conditions under which the script is exit()ing.
| While there is no universal standard for error codes, there are some
| broad conventions.  Three such conventions are mentioned below, for
| those who wish to make use of them.  The CodeIgniter defaults were
| chosen for the least overlap with these conventions, while still
| leaving room for others to be defined in future versions and user
| applications.
|
| The three main conventions used for determining exit status codes
| are as follows:
|
|    Standard C/C++ Library (stdlibc):
|       http://www.gnu.org/software/libc/manual/html_node/Exit-Status.html
|       (This link also contains other GNU-specific conventions)
|    BSD sysexits.h:
|       http://www.gsp.com/cgi-bin/man.cgi?section=3&topic=sysexits
|    Bash scripting:
|       http://tldp.org/LDP/abs/html/exitcodes.html
|
*/
defined('EXIT_SUCCESS')        || define('EXIT_SUCCESS', 0); // no errors
defined('EXIT_ERROR')          || define('EXIT_ERROR', 1); // generic error
defined('EXIT_CONFIG')         || define('EXIT_CONFIG', 3); // configuration error
defined('EXIT_UNKNOWN_FILE')   || define('EXIT_UNKNOWN_FILE', 4); // file not found
defined('EXIT_UNKNOWN_CLASS')  || define('EXIT_UNKNOWN_CLASS', 5); // unknown class
defined('EXIT_UNKNOWN_METHOD') || define('EXIT_UNKNOWN_METHOD', 6); // unknown class member
defined('EXIT_USER_INPUT')     || define('EXIT_USER_INPUT', 7); // invalid user input
defined('EXIT_DATABASE')       || define('EXIT_DATABASE', 8); // database error
defined('EXIT__AUTO_MIN')      || define('EXIT__AUTO_MIN', 9); // lowest automatically-assigned error code
defined('EXIT__AUTO_MAX')      || define('EXIT__AUTO_MAX', 125); // highest automatically-assigned error code

define('cache','?'.time());
define('profileFolder','https://files.ntalk.me/profile/');
define('timelineFolder','https://files.ntalk.me/timeline/');
define('galleryFolder','https://files.ntalk.me/gallery/');

define('region',array('서울'=>array(array('value'=>'종로','text'=>'종로구'),
                              array('value'=>'중구','text'=>'중구'),
                              array('value'=>'용산','text'=>'용산구'),
                              array('value'=>'성동','text'=>'성동구'),
                              array('value'=>'광진','text'=>'광진구'),
                              array('value'=>'동대문','text'=>'동대문구'),
                              array('value'=>'중랑','text'=>'중랑구'),
                              array('value'=>'성북','text'=>'성북구'),
                              array('value'=>'강북','text'=>'강북구'),
                              array('value'=>'도봉','text'=>'도봉구'),
                              array('value'=>'노원','text'=>'노원구'),
                              array('value'=>'은평','text'=>'은평구'),
                              array('value'=>'서대문','text'=>'서대문구'),
                              array('value'=>'마포','text'=>'마포구'),
                              array('value'=>'양천','text'=>'양천구'),
                              array('value'=>'강서','text'=>'강서구'),
                              array('value'=>'구로','text'=>'구로구'),
                              array('value'=>'금천','text'=>'금천구'),
                              array('value'=>'영등포','text'=>'영등포구'),
                              array('value'=>'동작','text'=>'동작구'),
                              array('value'=>'관악','text'=>'관악구'),
                              array('value'=>'서초','text'=>'서초구'),
                              array('value'=>'강남','text'=>'강남구'),
                              array('value'=>'송파','text'=>'송파구'),
                              array('value'=>'강동','text'=>'강동구')
                              ),
                        '부산'=>array(array('value'=>'중구','text'=>'중구'),
                            array('value'=>'서구','text'=>'서구'),
                            array('value'=>'동구','text'=>'동구'),
                            array('value'=>'영도','text'=>'영도구'),
                            array('value'=>'진구','text'=>'부산진구'),
                            array('value'=>'동래','text'=>'동래구'),
                            array('value'=>'남구','text'=>'남구'),
                            array('value'=>'북구','text'=>'북구'),
                            array('value'=>'강서','text'=>'강서구'),
                            array('value'=>'해운대','text'=>'해운대구'),
                            array('value'=>'사하','text'=>'사하구'),
                            array('value'=>'금정','text'=>'금정구'),
                            array('value'=>'연제','text'=>'연제구'),
                            array('value'=>'수영','text'=>'수영구'),
                            array('value'=>'사상','text'=>'사상구'),
                            array('value'=>'기장','text'=>'기장군')
                        ),
                        '인천'=>array(array('value'=>'중구','text'=>'중구'),
                            array('value'=>'동구','text'=>'동구'),
                            array('value'=>'남구','text'=>'남구'),
                            array('value'=>'연수','text'=>'연수구'),
                            array('value'=>'남동','text'=>'남동구'),
                            array('value'=>'부평','text'=>'부평구'),
                            array('value'=>'계양','text'=>'계양구'),
                            array('value'=>'서구','text'=>'서구'),
                            array('value'=>'강화','text'=>'강화군'),
                            array('value'=>'옹진','text'=>'옹진군')
                        ),
                        '대구'=>array(array('value'=>'중구','text'=>'중구'),
                            array('value'=>'동구','text'=>'동구'),
                            array('value'=>'서구','text'=>'서구'),
                            array('value'=>'남구','text'=>'남구'),
                            array('value'=>'북구','text'=>'북구'),
                            array('value'=>'수성','text'=>'수성구'),
                            array('value'=>'달서','text'=>'달서구'),
                            array('value'=>'달성','text'=>'달성군')
                        ),
                        '광주'=>array(array('value'=>'중구','text'=>'중구'),
                            array('value'=>'동구','text'=>'동구'),
                            array('value'=>'서구','text'=>'서구'),
                            array('value'=>'유성','text'=>'유성구'),
                            array('value'=>'대덕','text'=>'대덕구')
                        ),
                        '울산'=>array(array('value'=>'중구','text'=>'중구'),
                            array('value'=>'남구','text'=>'남구'),
                            array('value'=>'동구','text'=>'동구'),
                            array('value'=>'북구','text'=>'북구'),
                            array('value'=>'울주','text'=>'울주군')
                        ),
                        '세종'=>array(array('value'=>'조치원','text'=>'조치원'),
                            array('value'=>'연기','text'=>'연기면'),
                            array('value'=>'연동','text'=>'연동면'),
                            array('value'=>'부강','text'=>'부강면'),
                            array('value'=>'금남','text'=>'금남면'),
                            array('value'=>'장군','text'=>'장군면'),
                            array('value'=>'연서','text'=>'연서면'),
                            array('value'=>'전의','text'=>'전의면'),
                            array('value'=>'전동','text'=>'전동면'),
                            array('value'=>'소정','text'=>'소정면'),
                            array('value'=>'한솔','text'=>'한솔동'),
                            array('value'=>'새롬','text'=>'새롬동'),
                            array('value'=>'도담','text'=>'도담동'),
                            array('value'=>'아름','text'=>'아름동'),
                            array('value'=>'종촌','text'=>'종촌동'),
                            array('value'=>'고운','text'=>'고운동'),
                            array('value'=>'소담','text'=>'소담동'),
                            array('value'=>'보람','text'=>'보람동'),
                            array('value'=>'대평','text'=>'대평동')
                        ),
                        '경기'=>array(array('value'=>'가평','text'=>'가평군'),
                            array('value'=>'고양','text'=>'고양시'),
                            array('value'=>'과천','text'=>'과천시'),
                            array('value'=>'광명','text'=>'광명시'),
                            array('value'=>'광주','text'=>'광주시'),
                            array('value'=>'구리','text'=>'구리시'),
                            array('value'=>'군포','text'=>'군포시'),
                            array('value'=>'김포','text'=>'김포시'),
                            array('value'=>'남양주','text'=>'남양주시'),
                            array('value'=>'동두천','text'=>'동두천시'),
                            array('value'=>'부천','text'=>'부천시'),
                            array('value'=>'성남','text'=>'성남시'),
                            array('value'=>'수원','text'=>'수원시'),
                            array('value'=>'시흥','text'=>'시흥시'),
                            array('value'=>'안산','text'=>'안산시'),
                            array('value'=>'안성','text'=>'안성시'),
                            array('value'=>'안양','text'=>'안양시'),
                            array('value'=>'양주','text'=>'양주시'),
                            array('value'=>'양평','text'=>'양평군'),
                            array('value'=>'여주','text'=>'여주시'),
                            array('value'=>'연천','text'=>'연천군'),
                            array('value'=>'오산','text'=>'오산시'),
                            array('value'=>'용인','text'=>'용인시'),
                            array('value'=>'의왕','text'=>'의왕시'),
                            array('value'=>'의정부','text'=>'의정부시'),
                            array('value'=>'이천','text'=>'이천시'),
                            array('value'=>'파주','text'=>'파주시'),
                            array('value'=>'평택','text'=>'평택시'),
                            array('value'=>'포천','text'=>'포천시'),
                            array('value'=>'하남','text'=>'하남시')
                        ),
                        '강원'=>array(array('value'=>'원주','text'=>'원주시'),
                            array('value'=>'춘천','text'=>'춘천시'),
                            array('value'=>'강릉','text'=>'강릉시'),
                            array('value'=>'동해','text'=>'동해시'),
                            array('value'=>'속초','text'=>'속초시'),
                            array('value'=>'삼척','text'=>'삼척시'),
                            array('value'=>'홍천','text'=>'홍천군'),
                            array('value'=>'태백','text'=>'태백시'),
                            array('value'=>'철원','text'=>'철원군'),
                            array('value'=>'횡성','text'=>'횡성군'),
                            array('value'=>'평창','text'=>'평창군'),
                            array('value'=>'영월','text'=>'영월군'),
                            array('value'=>'정선','text'=>'정선군'),
                            array('value'=>'인제','text'=>'인제군'),
                            array('value'=>'고성','text'=>'고성군'),
                            array('value'=>'양양','text'=>'양양군'),
                            array('value'=>'화천','text'=>'화천군'),
                            array('value'=>'양구','text'=>'양구군')
                        ),
                        '충북'=>array(array('value'=>'청주','text'=>'청주시'),
                            array('value'=>'충주','text'=>'충주시'),
                            array('value'=>'제천','text'=>'제천시'),
                            array('value'=>'보은','text'=>'보은군'),
                            array('value'=>'옥천','text'=>'옥천군'),
                            array('value'=>'영동','text'=>'영동군'),
                            array('value'=>'증평','text'=>'증평군'),
                            array('value'=>'진천','text'=>'진천군'),
                            array('value'=>'괴산','text'=>'괴산군'),
                            array('value'=>'음성','text'=>'음성군'),
                            array('value'=>'단양','text'=>'단양군')
                        ),
                        '충남'=>array(array('value'=>'천안','text'=>'천안시'),
                            array('value'=>'공주','text'=>'공주시'),
                            array('value'=>'보령','text'=>'보령시'),
                            array('value'=>'아산','text'=>'아산시'),
                            array('value'=>'서산','text'=>'서산시'),
                            array('value'=>'논산','text'=>'논산시'),
                            array('value'=>'계룡','text'=>'계룡시'),
                            array('value'=>'당진','text'=>'당진시'),
                            array('value'=>'금산','text'=>'금산군'),
                            array('value'=>'부여','text'=>'부여군'),
                            array('value'=>'서천','text'=>'서천군'),
                            array('value'=>'청양','text'=>'청양군'),
                            array('value'=>'홍성','text'=>'홍성군'),
                            array('value'=>'예산','text'=>'예산군'),
                            array('value'=>'태안','text'=>'태안군')
                        ),
                        '경북'=>array(array('value'=>'포항','text'=>'포항시'),
                            array('value'=>'경주','text'=>'경주시'),
                            array('value'=>'김천','text'=>'김천시'),
                            array('value'=>'안동','text'=>'안동시'),
                            array('value'=>'구미','text'=>'구미시'),
                            array('value'=>'영주','text'=>'영주시'),
                            array('value'=>'영천','text'=>'영천시'),
                            array('value'=>'상주','text'=>'상주시'),
                            array('value'=>'문경','text'=>'문경시'),
                            array('value'=>'경산','text'=>'경산시'),
                            array('value'=>'군위','text'=>'군위군'),
                            array('value'=>'의성','text'=>'의성군'),
                            array('value'=>'청송','text'=>'청송군'),
                            array('value'=>'영양','text'=>'영양군'),
                            array('value'=>'영덕','text'=>'영덕군'),
                            array('value'=>'청도','text'=>'청도군'),
                            array('value'=>'고령','text'=>'고령군'),
                            array('value'=>'성주','text'=>'성주군'),
                            array('value'=>'칠곡','text'=>'칠곡군'),
                            array('value'=>'예천','text'=>'예천군'),
                            array('value'=>'봉화','text'=>'봉화군'),
                            array('value'=>'울진','text'=>'울진군'),
                            array('value'=>'울릉','text'=>'울릉군')
                        ),
                        '경남'=>array(array('value'=>'창원','text'=>'창원시'),
                            array('value'=>'김해','text'=>'남구'),
                            array('value'=>'진주','text'=>'남구'),
                            array('value'=>'양산','text'=>'남구'),
                            array('value'=>'거제','text'=>'남구'),
                            array('value'=>'통영','text'=>'남구'),
                            array('value'=>'사천','text'=>'남구'),
                            array('value'=>'밀양','text'=>'남구'),
                            array('value'=>'함안','text'=>'남구'),
                            array('value'=>'거창','text'=>'남구'),
                            array('value'=>'창녕','text'=>'남구'),
                            array('value'=>'고성','text'=>'남구'),
                            array('value'=>'하동','text'=>'남구'),
                            array('value'=>'합천','text'=>'남구'),
                            array('value'=>'남해','text'=>'남구'),
                            array('value'=>'함양','text'=>'남구'),
                            array('value'=>'산청','text'=>'남구'),
                            array('value'=>'의령','text'=>'남구')
                        ),
                        '전북'=>array(array('value'=>'전주','text'=>'전주시'),
                            array('value'=>'익산','text'=>'익산시'),
                            array('value'=>'군산','text'=>'군산시'),
                            array('value'=>'정읍','text'=>'정읍시'),
                            array('value'=>'완주','text'=>'완주군'),
                            array('value'=>'김제','text'=>'김제시'),
                            array('value'=>'남원','text'=>'남원시'),
                            array('value'=>'고창','text'=>'고창군'),
                            array('value'=>'부안','text'=>'부안군'),
                            array('value'=>'임실','text'=>'임실군'),
                            array('value'=>'순창','text'=>'순창군'),
                            array('value'=>'진안','text'=>'진안군'),
                            array('value'=>'장수','text'=>'장수군'),
                            array('value'=>'무주','text'=>'무주군')
                        ),
                        '전남'=>array(array('value'=>'여수','text'=>'여수시'),
                            array('value'=>'순천','text'=>'순천시'),
                            array('value'=>'목포','text'=>'목포시'),
                            array('value'=>'광양','text'=>'광양시'),
                            array('value'=>'나주','text'=>'나주시'),
                            array('value'=>'무안','text'=>'무안군'),
                            array('value'=>'해남','text'=>'해남군'),
                            array('value'=>'고흥','text'=>'고흥군'),
                            array('value'=>'화순','text'=>'화순군'),
                            array('value'=>'영암','text'=>'영암군'),
                            array('value'=>'영광','text'=>'영광군'),
                            array('value'=>'완도','text'=>'완도군'),
                            array('value'=>'담양','text'=>'담양군'),
                            array('value'=>'장성','text'=>'장성군'),
                            array('value'=>'보성','text'=>'보성군'),
                            array('value'=>'신안','text'=>'신안군'),
                            array('value'=>'장흥','text'=>'장흥군'),
                            array('value'=>'강진','text'=>'강진군'),
                            array('value'=>'함평','text'=>'함평군'),
                            array('value'=>'진도','text'=>'진도군'),
                            array('value'=>'곡성','text'=>'곡성군'),
                            array('value'=>'구례','text'=>'구례군')
                        ),
                        '제주'=>array(array('value'=>'제주','text'=>'제주시'),
                            array('value'=>'서귀포','text'=>'서귀포시')
                        )
                   )
);


//                   )


