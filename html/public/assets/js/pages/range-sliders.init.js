$(document).ready(function(){
    $("#range_01").ionRangeSlider(),
        $("#range_02").ionRangeSlider({min:100,max:1e3,from:550}),
        $("#range_03").ionRangeSlider({type:"double",grid:!0,min:0,max:1e3,from:200,to:800,prefix:"$"}),
        $("#range_04").ionRangeSlider({type:"double",grid:!0,min:-1e3,max:1e3,from:-500,to:500}),
        $("#range_05").ionRangeSlider({type:"double",grid:!0,min:-1e3,max:1e3,from:-500,to:500,step:250}),
        $("#range_06").ionRangeSlider({grid:!0,from:3,values:["January","February","March","April","May","June","July","August","September","October","November","December"]}),
        $("#range_07").ionRangeSlider({grid:!0,min:1e3,max:1e6,from:2e5,step:1e3,prettify_enabled:!0}),$("#range_08").ionRangeSlider({min:100,max:1e3,from:550,disable:!0})});