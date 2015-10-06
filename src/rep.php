<?php
require_once 'php/templates/header.php';

?>
<div class="page-header">
<h1>Report Example</h1>
</div>
<div class="row">
<div class="col-md-6">
  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">Count per pipeline</h3>
    </div>
    <div class="panel-body">
      <div id="table">
          <div id="tasks_mine_panel_count" class=" dataTables_processing" style=" position: absolute;">
            <div >Loading...</div>
          </div>
      </div>
    </div>
  </div>
</div>
<div class="col-md-6">
  <div class="panel panel-default">
    <div class="panel-heading">
      <h3 class="panel-title">Count per Activity</h3>
    </div>
    <div class="panel-body">
      <div id="table_act">
          <div id="tasks_mine_panel_act" class=" dataTables_processing" style=" position: absolute;">
            <div >Loading...</div>
          </div>

      </div>
    </div>
  </div>
</div>
</div>
</div>
<?php
require_once('php/templates/footer.php');
?>
<script type="text/javascript">

  var svg = dimple.newSvg("#table", '100%', 300);
  var url = "reports/count.php";
  d3.json(url, function (data2) {
      var myChart = new dimple.chart(svg, data2);
      myChart.setBounds(45, 30, '90%', 230)
      myChart.addCategoryAxis("x", ["Pipeline","Status"]);
      myChart.addMeasureAxis("y", "Count");
      myChart.addSeries("Status", dimple.plot.bar);
      myChart.addLegend(60, 10, 400, 30, "left");
      myChart.draw();
      $( '#tasks_mine_panel_count' ).fadeOut("fast");
  });

  var svg1 = dimple.newSvg("#table_act", '100%', 300);
  var url = "reports/count.act.php";
  d3.json(url, function (data) {
      var myChart = new dimple.chart(svg1, data);
      myChart.setBounds(45, 30, '90%', 230)
      myChart.addCategoryAxis("x", ["Activity","Status"]);
      myChart.addMeasureAxis("y", "Count");
      myChart.addSeries("Status", dimple.plot.bar);
      myChart.addLegend(60, 10, 400, 30, "left");
      myChart.draw();
      $( '#tasks_mine_panel_act' ).fadeOut("fast");
  });

</script>

