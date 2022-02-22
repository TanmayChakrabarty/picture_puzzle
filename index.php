<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<title>Image Rearrenging Puzzle</title>
		<script type="text/javascript" src="jquery-3.3.1.min.js"></script>
		<script type="text/javascript" src="jquery-ui.min.js"></script>
        <link type="text/css" rel="stylesheet" href="styles.css" />
		<style type="text/css">
		body{
			margin:0 auto;
			width:1000px;
			}
		#the_puzzle_container{
			float:left;
			position:relative;
			margin-bottom:10px;
			}
		#the_puzzle_final{
			float:right;
			position:relative;
			margin-bottom:10px;
			}
		#the_puzzle_final > img{
			box-shadow: 0 0 10px #999;
			}
		.each_slice,.fake_empty{
			cursor:pointer;
			margin:1px;
			overflow: hidden;
			position:absolute;
			box-shadow:1px 1px 1px #666666;
			}
		.each_slice img,.fake_empty img{
			position:absolute;
			}
		.slice_id{
			display:none;
			}
		.fake_empty{
			z-index:-1;
			}
		.status_holder{
			clear:both;
			text-align:center;
			color:#f00;
			font-weight:bold;
			}
		.footer{
			overflow:auto;
			clear:both;
			border-top:2px solid #0BA4EE;
			background-color:#ABE1FE;
			padding:1px;
			border-left:1px solid #0BA4EE;
			border-right:1px solid #0BA4EE;
			border-bottom:1px solid #0BA4EE;
			}
		.status_highlight{
			background-color:#000;
			}
		</style>
	</head>
	
	<body>
		<div>
			<button onClick = "image_loaded(the_main_image);">Randomize</button>
            <button onClick = "next_image();">Next Image</button>
            <button onClick = "solve_this();">Solve This</button>
			<label><input type="checkbox" id="is_play_sound">Play Sound</label>
        </div>
        <br />
        <div style="text-align:center;font-weight:bold;">Click on a slice to move it, Organize the slices of the left puzzled image as the right correct image.</div>
        
        <div class="status_holder">
        	Slices are now randomly displaced.
        </div>
		<div style="overflow:auto;width:1000px;margin:0 auto;">
            <div id="the_puzzle_container">      	
            </div>
            
            <div id="the_puzzle_final">
                <img src="images/image_1.jpg" onload="image_loaded(this)" id="base_image" />
            </div>
        </div>
		<div class="footer">
        	<span style="float:left;">Created by <span style="color:#03C;">Tanmay Chakrabarty</span></span>
            <span style="float:right;">More at: <a href="http://tanmayonrun.blogspot.com">Tanmayonrun Blog</a></span>
        </div>
        <div class="solution">
        	
        </div>
;
		
		<script type="text/javascript">
		console.log("Not Loaded");
		var move_sound = new Audio("audio/move.wav");
		move_sound.addEventListener('loadeddata', sound_loaded, false);
		function sound_loaded(){
			console.log("Loaded");
			}
		var solving = false;
		var current_image = 1;
		var image_array = new Array();
		image_array[1] = "images/image_1.jpg";
		image_array[2] = "images/image_2.jpg";
		image_array[3] = "images/image_3.jpg";
		image_array[4] = "images/image_4.jpg";
		image_array[5] = "images/image_5.jpg";
		image_array[6] = "images/image_6.jpg";
		image_array[7] = "images/image_7.jpg";
		
		var speed = 5;
		var moves_per_pixel = 5;
		var game_started = false;
		var random_steps = 100;
		
		var the_main_image="";
		var number_of_moves=0;
		var img_height=0;
		var img_width=0;
		var total_slice=0;
		var count_row_slices=4;
		var count_col_slices=4;
		var each_slice_width=0;
		var each_slice_height=0;
		var move_with_animation = false;
		var path_to_solution="";
		var bot_moves = new Array();
		var user_moves = new Array();
		var merged_moves = new Array();
		function solve_this(){
			if(!game_started) return;
			if(solving) return;
			speed = 5;
			move_with_animation = true;
			solving = true;
			revert_user_moves();
			}
		function revert_bot_moves(){
			if(bot_moves.length > 0){
				highlight_status("Solving the puzzle.");
				var i = bot_moves.length - 1;
				var data = bot_moves[i].split(",");
				var target_slice = $("#the_puzzle_container > div").eq(data[0]);
				
				alter_position(target_slice,data[1],"revert");
				i--;
				if(i >= 0){
					var reverting = setInterval(function(){
						if(i < 0 ){
							clearInterval(reverting);
							speed = 10;
							solving = false;
							checkmate("bot");
							}
						else{
							data = bot_moves[i].split(",");
							target_slice = $("#the_puzzle_container > div").eq(data[0]);
							alter_position(target_slice,data[1],"revert");
							i--;
							}
						},500);
					}
				}
			}
		function revert_user_moves(){
			if(user_moves.length > 0){
				highlight_status("Reverting the moves you made.");
				var i = user_moves.length - 1;
				var data = user_moves[i].split(",");
				var target_slice = $("#the_puzzle_container > div").eq(data[0]);
				
				alter_position(target_slice,data[1],"revert");
				i--;
				if(i >= 0){
					var reverting = setInterval(function(){
						if(i < 0 ){
							clearInterval(reverting);
							revert_bot_moves();
							}
						else{
							data = user_moves[i].split(",");
							target_slice = $("#the_puzzle_container > div").eq(data[0]);
							alter_position(target_slice,data[1],"revert");
							i--;
							}
						},500);
					}	
				}
			else revert_bot_moves();
			}
		function next_image(){
			if(solving) return;
			checkmate("cancled");
			current_image++;
			if(current_image == image_array.length) current_image = 1;
			$("#base_image").attr("src",image_array[current_image]);
			}
		function image_loaded(the_image){
			if(solving) return;
			$("#the_puzzle_container").html("");
			the_main_image=the_image;
			img_height=$(the_image).height();
			img_width=$(the_image).width();
			each_slice_width=img_width/4;
			each_slice_height=img_height/4;
			$("#the_puzzle_container").width(img_width+4*2).height(img_height+4*2);
			$("#the_puzzle_final").width(img_width).height(img_height).css("padding",4);
			set_the_slices(the_image);
			}
		function set_the_slices(the_image){
			var t=$(the_image).attr("src");
			var n=0;
			var r=0;
			var s=1;
			for(i=0;i<count_row_slices;i++){
				var o=0;
				var u=0;
				for(j=0;j<count_col_slices;j++){
					if(i==0&&j==0){
						var a="<div id='empty' class='each_slice' style='top:0px;left:0px;'><img src='images/empty.png' style='top:"+r+"px;left:"+o+"px;' /><span class='slice_id'>"+s+"</span></div>";
						a+="<div class='fake_empty' style='height:"+each_slice_height+"px;width:"+each_slice_width+"px;top:0px;left:0px;'><img src='images/empty.png' style='top:"+r+"px;left:"+o+"px;' /></div>";
						var f="<div class='blocked_part' style='height:"+each_slice_height+"px;width:"+each_slice_width+"px;position:absolute;top:4px;left:4px;'><img src='images/empty.png'/></div>";
						$("#the_puzzle_final").append(f)
						}
					else{
						var a="<div class='each_slice' style='top:"+n+"px;left:"+u+"px;'><img src='"+t+"' style='top:"+r+"px;left:"+o+"px;' /><span class='slice_id'>"+s+"</span></div>";
						a+="<div class='fake_empty' style='height:"+each_slice_height+"px;width:"+each_slice_width+"px;top:"+n+"px;left:"+u+"px;'><img src='images/empty.png' /></div>";
						}
					$("#the_puzzle_container").append(a);
					o-=each_slice_width;
					u+=each_slice_width+2;
					s++;
					}
				r-=each_slice_height;
				n+=each_slice_height+2;
				}
			$(".each_slice").height(each_slice_height).width(each_slice_width);
			do_randomize();
			}
		function do_randomize(){
			move_with_animation = false;
			path_to_solution="";
			number_of_moves=0;
			highlight_status("Slices are now randomly displaced.");
			var i = 0;
			
			while(i < random_steps){
				var slice_number = getRandomInt(0,15);
				if(get_target_slice_and_move(slice_number)){
					i++;
					if(i == random_steps) game_started = true;
					}
				}
			}
		function get_target_slice_and_move(slice_number){
			var this_slice = $(".each_slice").eq(slice_number);
			var target_position = can_this_slice_be_moved(this_slice);
			if(target_position) return alter_position(this_slice,target_position,"bot");
			else return 0;
			}
		function can_this_slice_be_moved(the_slice){
			//will return the target direction for the empty_slice if possible and 0 otherwise.
			//1 = this slice is at the left of the empty slice
			//2 = this slice is at the top of the empty slice
			//3 = this slice is at the right of the empty slice
			//4 = this slice is at the bottom of the empty slice
			var distance_x = each_slice_width + 2;
			var distance_y = each_slice_height + 2;
			var empty_position = $("#empty").position();
			var slice_position = $(the_slice).position();
			if(slice_position.left + distance_x == empty_position.left && slice_position.top == empty_position.top) return 1;
			else if(slice_position.top + distance_y == empty_position.top && slice_position.left == empty_position.left) return 2;
			else if(slice_position.left - distance_x == empty_position.left && slice_position.top == empty_position.top) return 3;
			else if(slice_position.top - distance_y == empty_position.top && slice_position.left == empty_position.left) return 4;
			else return 0;
			}
		function play_sound(the_sound_of){
			if(!$("#is_play_sound").is(":checked")) return;
			if(the_sound_of == "move"){
				move_sound.currentTime = 0;
				move_sound.play();
				}
			}
		function alter_position(target_slice,target_at, altered_by){
			
			if(altered_by == "bot") bot_moves[bot_moves.length] = $(target_slice).index() + "," + return_oposite_move(target_at);
			else if(altered_by == "user"){
				user_moves[user_moves.length] = $(target_slice).index() + "," + return_oposite_move(target_at);
				play_sound("move");
				}
			
			var empty = $("#empty");
			var empty_position = $(empty).position();
			var target_position = $(target_slice).position(); 
			if(move_with_animation){
				if(target_at == 1){
					var left = target_position.left;
					var animation = setInterval(function(){
						if(left + moves_per_pixel <= empty_position.left){
							left += moves_per_pixel;
							$(target_slice).css("left",left);
							}
						else{
							$(target_slice).css("left",empty_position.left);
							clearInterval(animation);
							}
						},speed);
					}
				else if(target_at == 3){
					var left = target_position.left;
					var animation = setInterval(function(){
						if(left - moves_per_pixel >= empty_position.left){
							left -= moves_per_pixel;
							$(target_slice).css("left",left);
							}
						else{
							$(target_slice).css("left",empty_position.left);
							clearInterval(animation);
							}
						},speed);
					}
				else if(target_at == 2){
					var top = target_position.top;
					var animation = setInterval(function(){
						if(top + moves_per_pixel <= empty_position.top){
							top += moves_per_pixel;
							$(target_slice).css("top",top);
							}
						else{
							$(target_slice).css("top",empty_position.top);
							clearInterval(animation);
							}
						},speed);
					}
				else if(target_at == 4){	
					var top = target_position.top;
					var animation = setInterval(function(){
						if(top - moves_per_pixel >= empty_position.top){
							top -= moves_per_pixel;
							$(target_slice).css("top",top);
							}
						else{
							$(target_slice).css("top",empty_position.top);
							clearInterval(animation);
							}
						},speed);
					}
				}
			else{
				$(target_slice).css("top",empty_position.top).css("left",empty_position.left);
				}
			$(empty).css("top",target_position.top).css("left",target_position.left);
			var s = $(".slice_id",empty).text();
			$(".slice_id",empty).text($(".slice_id",target_slice).text());
			$(".slice_id",target_slice).text(s);
			return 1;
			}
		$(".each_slice").on("click",function(){
			if(solving) return;
			if(!game_started){
				highlight_status("start game by clicking Randomize button or Next Image");
				return;
				}
			move_with_animation = true;
			var target_position = can_this_slice_be_moved(this);
			if(target_position){
				alter_position(this,target_position,"user");
				number_of_moves++;;
				checkmate("user");
				}
			});
		function return_oposite_move(move){
			if(move == 1) return 3;
			else if(move == 2) return 4;
			else if(move == 3) return 1;
			else if(move == 4) return 2;
			}
		function getRandomInt(e,t){
			return Math.floor(Math.random()*(t-e+1))+e;
			}
		function checkmate(caller){
			var result = "no";
			if(caller == "user"){
				var n=1;
				var r=1;
				var i=0;
				$(".slice_id").each(function(e,t){
					if(parseInt($(t).text())==r)i++;
					r++;
					});
				highlight_status(i+" slices are in corrct position. "+number_of_moves+" moves made.");
				
				if(i==16){
					var s="Congratulations :: Puzzle Solved";
					s+="\n\nYou took "+number_of_moves+" moves to solve the puzzle.";
					alert(s);
					result = "yes";
					highlight_status(i+" slices are in corrct position. "+number_of_moves+" moves made.")
					}
				}
			else if(caller == "bot"){
				result = "yes";
				highlight_status("The puzzle has been solved, you failed.<br />To play again, click on Randomize or choose another image to solve.");
				}
			else if(caller == "cancled"){
				result = "yes";
				}
			if(result == "yes"){
				number_of_moves = 0;
				bot_moves = [];
				user_moves = [];
				merged_moves = [];
				game_started = false;
				}
			}
		function highlight_status(msg){
			$(".status_holder").html(msg);
			$(".status_holder").addClass("status_highlight",500,function(){
				$(".status_holder").removeClass("status_highlight",500);
				});
			}
        </script>
	</body>
</html>



