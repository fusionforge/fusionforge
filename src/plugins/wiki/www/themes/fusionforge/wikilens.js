// Wikilens Javascript functions.
// $Id: wikilens.js 7138 2009-09-17 10:11:31Z rurban $

// Globals:

/*
var data_path = '/phpwiki-cvs';
var pagename  = 'HomePage';
var script_url= '/wikicvs';
var stylepath = data_path+'/themes/MonoBook/';
*/

var rating = new Array; var prediction = new Array;
var avg = new Array; var numusers = new Array;
var canRate = new Array;
var msg_rating_votes = "Rating: %.1f (%d votes)";
var msg_curr_rating = "Your current rating: ";
var msg_curr_prediction = "Your current prediction: ";
//var msg_chg_rating = "Change your rating from ";
var msg_to = " to ";
var msg_add_rating = "Add your rating: ";
var msg_thanks = "Thanks!";
var msg_rating_deleted = "Rating deleted!";

//var rateit_imgsrc = '/phpwiki-cvs/themes/MonoBook/images/RateIt';
var rateit_imgsrc = '/plugins/wiki/themes/fusionforge/images/RateIt';
var rateit_action = 'RateIt';
var idTop = '';
//

function displayRating(imgId, imgPrefix, ratingvalue, pred, init) {
  var ratings = new Array('Not Rated','Awful','Very Poor','Poor','Below Average',
			  'Average','Above Average','Good','Very Good','Excellent','Outstanding');
  var cancel = imgId + imgPrefix + 'Cancel';
  var curr_rating = rating[imgId];
  var curr_pred = prediction[imgId];
  var title = '';
  var imgSrc = rateit_imgsrc;
  if (init) { // re-initialize titles
    title = msg_curr_rating+curr_rating+' '+ratings[curr_rating*2];
    var linebreak = '. '; //&#xD or &#13 within IE only;
    if (pred) {
      title = title+' '+msg_curr_prediction+ curr_pred+' '+ratings[curr_pred*2];
    }
	if (canRate[imgId]) {
		if (curr_rating) {
			document[cancel].style.display = 'inline';
		}
		else {
			document[cancel].style.display = 'none';
		}
	}
  }
  for (var i=1; i<=10; i++) {
    var imgName = imgId + i;
    if (init) {
      //document[imgName].title = title;
      var j = i/2;
      if (ratingvalue > 0) {
        if (curr_rating) {
	  document[imgName].onmouseout = function() { displayRating(imgId,imgPrefix,avg[imgId],0,0) };
        } else if (curr_pred) {
	  document[imgName].onmouseout = function() { displayRating(imgId,imgPrefix,curr_pred,1,0) };
        }
        if (curr_rating != ratingvalue && typeof(msg_chg_rating) != "undefined") {
          document[imgName].title = msg_chg_rating+curr_rating+' '+ratings[curr_rating*2]+msg_to+j+' '+ratings[i];
	} 
      } else {
	document[imgName].onmouseout = function() { displayRating(imgId,imgPrefix,avg[imgId],0,0) };
        document[imgName].title = msg_add_rating+j+' '+ratings[i];
      }
    }
    var imgType = 'N';
    if (pred) {
      if (init)
        document[imgName].title = title+linebreak+msg_add_rating+ratings[i];
      imgType = 'R';
    } else if (i<=(ratingvalue*2)) {
      imgType = 'O';
    }
    document[imgName].src = imgSrc + imgPrefix + imgType + ((i%2) ? 'k1' : 'k0') + '.png';
  }
}

function displayRating2(imgId, imgPrefix, ratingvalue, pred, init) {
  var ratings = new Array('Not Rated','Awful','Very Poor','Poor','Below Average',
			  'Average','Above Average','Good','Very Good','Excellent','Outstanding');
  var cancel = imgId + imgPrefix + 'Cancel';
  var curr_rating = rating[imgId];
  var curr_pred = prediction[imgId];
  var title = '';
  var imgSrc = rateit_imgsrc;
  if (init) { // re-initialize titles
    title = msg_curr_rating+curr_rating+' '+ratings[curr_rating*2];
    var linebreak = '. '; //&#xD or &#13 within IE only;
    if (pred) {
      title = title+' '+msg_curr_prediction+ curr_pred+' '+ratings[curr_pred*2];
    }
	if (curr_rating) {
	  document[cancel].style.display = 'inline';
	}
	else {
	  document[cancel].style.display = 'none';
	}
  }
  for (var i=1; i<=10; i++) {
    var imgName = imgId + i;
    if (init) {
      //document[imgName].title = title;
      var j = i/2;
      if (ratingvalue > 0) {
        if (curr_rating) {
	  document[imgName].onmouseout = function() { displayRating(imgId,imgPrefix,avg[imgId],0,0) };
        } else if (curr_pred) {
	  document[imgName].onmouseout = function() { displayRating(imgId,imgPrefix,curr_pred,1,0) };
        }
        if (curr_rating != ratingvalue && typeof(msg_chg_rating) != "undefined") {
          document[imgName].title = msg_chg_rating+curr_rating+' '+ratings[curr_rating*2]+msg_to+j+' '+ratings[i];
	} 
      } else {
	document[imgName].onmouseout = function() { displayRating(imgId,imgPrefix,avg[imgId],0,0) };
        document[imgName].title = msg_add_rating+j+' '+ratings[i];
      }
    }
    var imgType = 'N';
    if (pred) {
      if (init)
        document[imgName].title = title+linebreak+msg_add_rating+ratings[i];
      imgType = 'R';
	 } else {
		if (ratingvalue <= curr_rating) {
			if (i <= (ratingvalue * 2)) 
				imgType = 'Rp';
			else 
				if (i <= (curr_rating * 2)) 
					imgType = 'R';
		}
		else {
			if (i <= (curr_rating * 2)) 
				imgType = 'Rp';
			else 
				if (i <= (ratingvalue * 2)) 
					imgType = 'Rm';
		}
    }
    document[imgName].src = imgSrc + imgPrefix + imgType + ((i%2) ? 'k1' : 'k0') + '.png';
  }
}

function sprintfRating(s, num, count) {
	var num1 = Math.round(num * 10) / 10;
	if (count < 2) s = s.replace(/votes/, 'vote');
    return s.replace(/\%.1f/, num1).replace(/\%d/, count);
}
function clickRating(imgPrefix,pagename,version,imgId,dimension,newrating) {
  var actionImg = imgId+'Action';
  var top = document.getElementById('rateit-widget-top');
  var nusers = numusers[imgId];
  var old_rating = rating[imgId];
  if (newrating == 'X') {
    deleteRating(actionImg,pagename,dimension);
    if (top && nusers) {
        var sum1 = avg[imgId] * nusers;
        var new_avg;
        if (old_rating && old_rating > 0) {
		  if (nusers > 1) {
		  	new_avg = (sum1 - old_rating) / (nusers - 1);
		  	numusers[imgId]--;
		  }
		  else {
		  	new_avg = 0;
		  	numusers[imgId] = 0;
		  }
		}
        if (new_avg.toString() != "NaN" && idTop == imgId) {
			avg[imgId] = new_avg;
            top.childNodes[0].innerHTML = sprintfRating(msg_rating_votes, new_avg, nusers-1);
        }
    }
    rating[imgId] = 0;
	displayRating2(imgId,imgPrefix,0,0,1);
  } else {
    submitRating(actionImg,pagename,version,dimension,newrating);
    if (top && nusers) {
        var sum1 = avg[imgId] * nusers;
		var new_avg;
        if (old_rating && (old_rating > 0)) {
            new_avg = (sum1 + newrating - old_rating)  / nusers;
        } else {
            new_avg = (sum1 + newrating) / (nusers + 1);
            numusers[imgId]++;
        }
		if (new_avg.toString() != "NaN" && idTop == imgId) {
			avg[imgId] = new_avg;
			if (newrating != rating[imgId]) {
				top.childNodes[0].innerHTML = sprintfRating(msg_rating_votes, new_avg, numusers[imgId]);
			}
		}
    } else if (top && idTop == imgId) {
        top.childNodes[0].innerHTML = sprintfRating(msg_rating_votes, newrating, 1);
        avg[imgId] = newrating;
        numusers[imgId] = 1;
    }
    rating[imgId] = newrating;
	displayRating2(imgId,imgPrefix,newrating,0,1);
  }
}
function submitRating(actionImg,page,version,dimension,newrating) {
  //TODO: GET => PUT request
  // currently ratings are changed with side-effect, but GET should be side-effect free.
  var myRand = Math.round(Math.random()*(1000000));
  var imgSrc = WikiURL(page) + 'version=' + version + '&action=' + rateit_action + '&mode=add&rating=' + newrating + '&dimension=' + dimension + '&nocache=1&nopurge=1&rand=' + myRand;
  //alert('submitRating("'+actionImg+'", "'+page+'", '+version+', '+dimension+', '+newrating+') => '+imgSrc);
  document[actionImg].title = msg_thanks;
  document[actionImg].src = imgSrc;
}
function deleteRating(actionImg, page, dimension) {
  //TODO: GET => DELETE request
  // currently ratings are changed with side-effect, but GET should be side-effect free.
  var myRand = Math.round(Math.random()*(1000000));
  var imgSrc = WikiURL(page) + 'action=' + rateit_action + '&mode=delete&dimension=' + dimension + '&nocache=1&nopurge=1&rand=' + myRand;
  //alert('deleteRating("'+actionImg+'", "'+page+'", '+version+', '+dimension+')');
  document[actionImg].title = msg_rating_deleted;
  document[actionImg].src = imgSrc;
}
