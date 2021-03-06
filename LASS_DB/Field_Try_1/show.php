<php
/*
 To access the page: http://nrl.iis.sinica.edu.tw/LASS/show.php?site=中央研究院&city=台北市&district=南港區&device_id=FT1_001
 or simply: http://nrl.iis.sinica.edu.tw/LASS/show.php?device_id=FT1_001

 where site is the unique name of this node, 
       city is the deployment city, 
       district is the deployment districy/village, 
       channel is the channel ID of the corresponding ThingSpeak channel, 
       apikey is the READ key of the corresponding ThingSpeak channel
*/
?>
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script type="text/javascript" src="//thingspeak.com/highcharts-3.0.8.js"></script>
    <script type="text/javascript" src="//thingspeak.com/exporting.js"></script>

  <title>PM2.5 即時資訊</title>

  <style type="text/css">
  body { background-color: white; }
  #container { width: 100%; display: table; }
  #inner { vertical-align: top; display: table-cell; }
  #gauge_div { width: 450px; margin: 0 auto; }
  #chart-container { width: 800px; height: 250px; display: block; margin: 5px 15px 15px 0; overflow: hidden; }
</style>


  <script type='text/javascript' src='https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js'></script>
<script type='text/javascript' src='https://www.google.com/jsapi'></script>
<script type="text/javascript" src="http://maps.google.com/maps/api/js"></script>
<script type='text/javascript'>

  // maximum value for the gauge
  var max_gauge_value = 100;
  // name of the gauge
  var gauge_name = 'PM2.5';

  // global variables
  var chart, charts, data;

  // load the google gauge visualization
  google.load('visualization', '1', {packages:['gauge']});
  google.setOnLoadCallback(initChart);
  
  

  // display the data
  function displayData(point) {
    data.setValue(0, 0, gauge_name);
    data.setValue(0, 1, point);
    chart.draw(data, options);
  }

  // load the data
  function loadData() {
    // variable for the data point
    var p;
    var device_id = '<?php echo $_GET['device_id']; ?>';
    var geocoder = new google.maps.Geocoder();
    var coord;

    // get the data from thingspeak
    $.getJSON('http://nrl.iis.sinica.edu.tw/LASS/last.php', {device_id: escape(device_id)}, function(data) {

      // get the data point
      p = data.s_d0;

      if (p<=35){
        $('#suggestion1').html("正常戶外活動。");
        $('#suggestion2').html("正常戶外活動。");
      } else if (p<=53){
        $('#suggestion1').html("正常戶外活動。");
        $('#suggestion2').html("有心臟、呼吸道及心血管疾病的成人與孩童感受到癥狀時，應考慮減少體力消耗，特別是減少戶外活動。");
      } else if (p<=70){
        $('#suggestion1').html("任何人如果有不適，如眼痛，咳嗽或喉嚨痛等，應該考慮減少戶外活動。");
        $('#suggestion2').html("<ol><li>有心臟、呼吸道及心血管疾病的成人與孩童，應減少體力消耗，特別是減少戶外活動。</li><li>老年人應減少體力消耗。</li><li>具有氣喘的人可能需增加使用吸入劑的頻率。</li></ol>");
      } else {
        $('#suggestion1').html("任何人如果有不適，如眼痛，咳嗽或喉嚨痛等，應減少體力消耗，特別是減少戶外活動。");
        $('#suggestion2').html("<ol><li>有心臟、呼吸道及心血管疾病的成人與孩童，以及老年人應避免體力消耗，特別是避免戶外活動。</li><li>具有氣喘的人可能需增加使用吸入劑的頻率。</li></ol>");
      }


      // if there is a data point display it
      if (p) {
        p = Math.round((p / max_gauge_value) * 100);
        displayData(p);
      }

      p = data.s_t0;
      $('#temperature').html(p);

      p = data.s_h0;
      $('#humidity').html(p);

      p = new Date(data.timestamp)
      $('#lastupdate').html(p.toString());

<?php    if (isset($_GET['site']) and isset($_GET['city']) and isset($_GET['district'])){   ?>
	var address = "<?php echo $_GET['site']; ?>"
        address = address.concat("（<?php echo $_GET['city']; ?><?php echo $_GET['district']; ?>）");
        $('#location').html(address);
<?php    } else {  ?>
      var tmp, tmp2;
      gps_lat = data.gps_lat;
      tmp = gps_lat - Math.floor(gps_lat);
      tmp = tmp / 60 * 100 * 100;
      tmp2 = tmp - Math.floor(tmp);
      tmp2 = tmp2 * 100;
      gps_lat = Math.floor(gps_lat) + Math.floor(tmp) * 0.01 + tmp2 * 0.0001;

      gps_lon = data.gps_lon;
      tmp = gps_lon - Math.floor(gps_lon);
      tmp = tmp / 60 * 100 * 100;
      tmp2 = tmp - Math.floor(tmp);
      tmp2 = tmp2 * 100;
      gps_lon = Math.floor(gps_lon) + Math.floor(tmp) * 0.01 + tmp2 * 0.0001;

      coord = new google.maps.LatLng(gps_lat, gps_lon);
      geocoder.geocode({'latLng': coord }, function(results, status) {
         if (status == google.maps.GeocoderStatus.OK) {
            var level_1;
            var level_2;
            for (var x = 0, length_1 = results.length; x < length_1; x++){
              for (var y = 0, length_2 = results[x].address_components.length; y < length_2; y++){
                  var type = results[x].address_components[y].types[0];
                    if ( type === "administrative_area_level_1") {
                      level_1 = results[x].address_components[y].long_name;
                      if (level_2) break;
                    } else if (type === "locality"){
                      level_2 = results[x].address_components[y].long_name;
                      if (level_1) break;
                    }
                }
            }
            var address = results[0].formatted_address;
            var addr = address.split(',');
            address = addr[addr.length-3].concat(", ",addr[addr.length-2],", ",addr[addr.length-1]);

            $('#zipcode').html(addr[addr.length-1]);
            $('#location').html(address);
            //console.log(results[0]);
         }
      });
<?php     }   ?>
    });
    
  }

  // initialize the chart
  function initChart() {

    data = new google.visualization.DataTable();
    data.addColumn('string', 'Label');
    data.addColumn('number', 'Value');
    data.addRows(1);

    chart = new google.visualization.Gauge(document.getElementById('gauge_div'));
    options = {width: 450, height: 450, redFrom: 53, redTo: 100, yellowFrom:35, yellowTo: 53, greenFrom:0, greenTo:35, minorTicks: 5};

    loadData();

    // load new data every 15 seconds
    setInterval('loadData()', 15000);
  }

</script>


<script type="text/javascript">
  var device_id = '<?php echo $_GET['device_id']; ?>';
  var series_1_results = 2000;
  var series_1_color = '#ff0000';

  // chart title
  var chart_title = '';
  // y axis title
  var y_axis_title = 'PM2.5';

  // user's timezone offset
  var my_offset = new Date().getTimezoneOffset();
  // chart variable
  var my_chart;

  // when the document is ready
  $(document).on('ready', function() {
    // add a blank chart
    addChart();
    // add the first series
    addSeries(device_id, series_1_results, series_1_color);
  });

  // add the base chart
  function addChart() {
    // variable for the local date in milliseconds
    var localDate;

    // specify the chart options
    var chartOptions = {
      chart: {
        renderTo: 'chart-container',
        defaultSeriesType: 'line',
        backgroundColor: '#ffffff',
        events: { }
      },
      title: { text: chart_title },
      plotOptions: {
        series: {
          marker: { radius: 3 },
          animation: true,
          step: false,
          borderWidth: 0,
          turboThreshold: 0
        }
      },
      tooltip: {
        // reformat the tooltips so that local times are displayed
        formatter: function() {
          var d = new Date(this.x + (my_offset*60000));
          var n = (this.point.name === undefined) ? '' : '<br>' + this.point.name;
          return this.series.name + ':<b>' + this.y + '</b>' + n + '<br>' + d.toDateString() + '<br>' + d.toTimeString().replace(/\(.*\)/, "");
        }
      },
      xAxis: {
        type: 'datetime',
        title: { text: 'Date' }
      },
      yAxis: { title: { text: y_axis_title } },
      exporting: { enabled: false },
      legend: { enabled: false },
      credits: {
        text: '',
        href: '',

        style: { color: '#D62020' }
      }
    };

    // draw the chart
    my_chart = new Highcharts.Chart(chartOptions);
  }

  // add a series to the chart
  function addSeries(device, results, color) {
    //var field_name = 'field' + field_number;
    var field_name = 's_d0';

    // get the data with a webservice call
    $.getJSON('http://nrl.iis.sinica.edu.tw/LASS/history.php', {device_id: escape(device)}, function(data) {

      // blank array for holding chart data
      var chart_data = [];

      // iterate through each feed
      $.each(data.feeds, function() {
        var point = new Highcharts.Point();
        var value = this[field_name];
        point.x = getChartDate(this.timestamp);
        point.y = parseFloat(value);
        // if a numerical value exists add it
        if (!isNaN(parseInt(value))) { chart_data.push(point); }
      });

      // add the chart data
      //my_chart.addSeries({ data: chart_data, name: data.channel[field_name], color: color });
      my_chart.addSeries({ data: chart_data, name: "PM2.5", color: color });
    });
  }

  // converts date format from JSON
  function getChartDate(d) {
    // offset in minutes is converted to milliseconds and subtracted so that chart's x-axis is correct
    return Date.parse(d) - (my_offset * 60000);
  }

</script>

  </head>

  <body>
    <center>
      <h1>PM2.5 即時資訊：<span id="location"></span></h1>
      <font size="+2">
      時間：<span id="lastupdate"> </span><br>
      溫度：</b><span id="temperature"></span>&#8451;；濕度：</b><span id="humidity"></span>%
      </font>
    </center>

    <center>
    <table border=0 width=800><tr>
    <td width=450>
      <div id="inner">
        <div id="gauge_div"></div>
      </div>
    </td>
    <td width=350>
    <ul>
      <li><b>針對一般民眾的活動建議：</b><span id="suggestion1"></span></li>
      <li><b>針對敏感性族群的活動建議：</b><span id="suggestion2"></span></li>
    </ul>
    </td>
    </tr></table>

    <div id="chart-container">
      <img alt="Ajax loader" src="//thingspeak.com/assets/loader-transparent.gif"/>
    </div>
    <hr>
    註：以上量測結果仍屬實驗階段，其正確性與代表性僅供參考，正確資料仍以環保署公佈為主。
    <br>
    Powered by <a href="https://www.facebook.com/groups/1607718702812067/">LASS</a> & <A href="https://sites.google.com/site/cclljj/NRL">IIS-NRL</a> & <a href="https://thingspeak.com">ThingSpeak.com</a>
    </center>
  </body>
</html>
