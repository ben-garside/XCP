    </div> <!-- /container -->
 <footer class="footer">
       	<p class="text-center footer-text"><a target="_" href="https://github.com/ben-garside/XCP/releases/tag/<?php echo Config::get('release/version');?>"><?php echo Config::get('release/version') . "</a> - " . Config::get('release/date');?><br> <?php echo 'Server=' . Config::get('mysql/host') . ';Database=' . Config::get('mysql/db') .';User='. Config::get('mysql/username');?></p>       </footer>
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <script src="js/bootstrap.min.js"></script>

	<!-- Bootstrap Select CSS -->
	<link rel="stylesheet" type="text/css" href="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/css/bootstrap-select.min.css">
	<!-- Bootstrap Select JS -->
	<script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.6.3/js/bootstrap-select.min.js"></script>
	<!-- DataTables CSS -->
	<link rel="stylesheet" type="text/css" href="css/dataTables.bootstrap.css">
	<!-- DataTables JS -->
	<script type="text/javascript" charset="utf8" src="//cdn.datatables.net/1.10.5/js/jquery.dataTables.js"></script>
	<script type="text/javascript" charset="utf8" src="js/dataTables.bootstrap.js"></script>
	<script type="text/javascript" charset="utf8" src="js/jquery.color.js"></script>


	<script src="js/xcp.js"></script>
	<script type="text/javascript" charset="utf8" src="js/jquery.timeago.js"></script>
</html>
