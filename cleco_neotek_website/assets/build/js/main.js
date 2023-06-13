jQuery(document).ready(function ($) {

    /*** Add in the Dynamic and Ui content ***/
    loadContent(function() {
        /*** Display Page When Ready ***/
        setTimeout(function () {
            $('body').addClass('loaded');
            setTimeout(function () {
                $('#loader-wrapper').addClass("im-gone");
            },1000);
        }, 3000);
    });

    clickListeners();

    checkViewerHeight();
    $(window).on('resize', function () {
        checkViewerHeight();
    });

    $("main").scroll(function () {
        var scrollPos = $("main").scrollTop();
        var contactPos = $("#contact").offset().top;
        if (contactPos <= 400) {
            $(".popout-tab").removeClass("seeme");
        }else {
            if (scrollPos >= 400) {
                $('.popout-tab').addClass("seeme");
            }
        }
    });

    $(".card-body").scroll(function() {
        var cardPos = $(this).scrollLeft();
        var tableWidth = $(this).children('table').outerWidth();
        var cardWidth = $(this).width();
        var cardScroll = cardWidth + cardPos;
        var tableOffset = tableWidth - cardScroll;
        if (cardPos > 5) {
            $(this).addClass('shadow-right');
        } else {
            if ($(this).hasClass('shadow-right')) {
                $(this).removeClass('shadow-right');
            }
        }
        if (tableOffset == 20) {
            $(this).addClass("no-shadow-left");
        } else {
            $(this).removeClass("no-shadow-left");
        }
    });

});

function loadContent(callback) {
    hotspotLocation("#rightAngle");
    hotspotLocation("#pistol-1");
    hotspotLocation("#inline");

    buildProductTable(["#rAngle30", "rangle", "30"]);
    buildProductTable(["#rAngle50", "rangle", "50"]);
    buildProductTable(["#rAngle70", "rangle", "70"]);
    buildProductTable(["#pistol30", "pistol", "30"]);
    buildProductTable(["#pistol50", "pistol", "50"]);
    buildProductTable(["#inline30", "inline", "30"]);
    buildProductTable(["#inline50", "inline", "50"]);
    buildProductTable(["#inline70", "inline", "70"]);

    callback();
}

function clickListeners() {
    $(".cd-product-viewer-wrapper").on("click", function () {
        makeActive($(this));
    });
    $(".viewer-control a").on("click", function (e) {
        e.preventDefault();
        var getID = $(this).attr("href");
        if ($(".hotspot-modal").length) {
            if ($(".hotspot-modal").hasClass("view-spot")) {
                $(".hotspot-modal").removeClass("view-spot");
                setTimeout(function () {
                    $(".hotspot-modal").remove();
                }, 2000);
            } else {
                $(".hotspot-modal").remove();
            }
        }
        makeActive($(getID));
    });
    $("#homeNav .nav-link").on("click", function (e) {
        if (!$(this).hasClass('language')) {
            e.preventDefault();
            var getID = $(this).attr("href");
            scrollToAnchor(getID);
        }
    });

    $(".popout-tab").on('click', function (e) {
        e.preventDefault();
        var getID = $(this).attr("href");
        scrollToAnchor(getID);
    });
    $('.product-viewer label').on('click', function (e) {
        e.preventDefault();
        //var target = $(e.target);
        //if ($(this).css("opacity") == "1") {
        if ($('.hotspot-modal').length) {
            $(".hotspot-modal").remove();
        }
        var spotID = $(this).attr("data-spotname");
        spotID = "#" + spotID;

        var thisData = {
            toolID: "#" + $(this).closest(".cd-product-viewer-wrapper").attr("id"),
            isImage: "no",
            hotspot: $(this).attr("data-spotname"),
            tagline: $(this).attr("data-tagline")
        };
        //console.log(thisData.tagline);
        if ($('#' + thisData.hotspot).length) {
            viewVideo(spotID);
        } else {
            if ($(this).attr("data-spotimage") == "yes") {
                thisData.isImage = "yes";
            }
            buildPlayHotspot(thisData.toolID, thisData.hotspot, thisData.tagline, thisData.isImage, function () {
                var replay = $(spotID).find(".replay").length;
                if (replay == 0) {
                    if ($('body#deutsch').length) {
                        $(".view-spot").append(
                            '<span class="replay hide-me">wiederholung</spam>'
                        );
                    } else if ($('body#chinese').length) {
                        $(".view-spot").append(
                            '<span class="replay hide-me">重新播放</spam>'
                        );
                    } else if ($('body#espanol').length) {
                        $(".view-spot").append(
                            '<span class="replay hide-me">Reproducción</spam>'
                        );
                    } else {
                        $(".view-spot").append(
                            '<span class="replay hide-me">replay</spam>'
                        );
                    }
                }
                viewVideo(spotID);
            });
        }
        //}
    });
}

function viewVideo(hotSpotID) {
    console.log(hotSpotID);
    var thisVideo = $("video", hotSpotID).get(0);
    if (thisVideo != undefined) {
      setTimeout(function() {
        $(hotSpotID).addClass("view-spot");
        thisVideo.currentTime = 0;
      }, 500);
      setTimeout(function() {
        thisVideo.play();
        $("video").on("ended", function() {
          $(".replay").removeClass("hide-me");
        });
        $(".replay").on("click", function() {
          thisVideo.play();
          $(this).addClass("hide-me");
        });
      }, 1500);
      $(".tool-video").on("play", function(e) {
        var $child = $(".view-spot").find(".hide-me");
        if ($child.length == 0) {
          $(".replay").addClass("hide-me");
        }
      });
    } else {
      setTimeout(function() {
        $(hotSpotID).addClass("view-spot");
      }, 500);
    }
    $(".close").on("click", function(e) {
      e.preventDefault();
      $(this)
        .parent()
        .removeClass("view-spot");
    });
}

function checkViewerHeight() {

    var prodView = $(".product-viewer").height();
    prodView = prodView + 100;
    $(".product-display").css('height', prodView);
    var maxHeight = -1;
    $('.carousel-item').css('height','');

    $('.carousel-item').each(function() {
       maxHeight = maxHeight > $(this).height() ? maxHeight : $(this).height();
    });
    $('.carousel-item').each(function() {
     $(this).height(maxHeight);
   });


}

function scrollToAnchor(aid) {
    var curOffset = $("#overview").offset().top;
    var curSelect = $(aid).offset().top;
    var navOffset = $("#homeNav").height();
    var posControl = Math.abs(curOffset) + navOffset + curSelect;
    $("main").animate({ scrollTop: posControl }, "slow");
}

function buildProductTable(thisID) {
    var tableID = thisID[0];
    getJsonData([thisID[1], thisID[2]], function(data) {
        for (var i = 0; i < data.length; i++) {
            var dGroup = data[i];
            var newProductContent = "<tr><td>" + dGroup.model + "<a href='assets/specs/" + thisID[1] + "/" + dGroup.specSheet + "' target='_blank'><i class='fas fa-file-pdf'></i></a>" +
                "</td><td>" + dGroup.tminnm +
                "</td><td>" + dGroup.tmaxnm +
                "</td><td>" + dGroup.tminlb +
                "</td><td>" + dGroup.tmaxlb +
                "</td><td>" + dGroup.rpm +
                "</td><td>" + dGroup.weightkg +
                "</td><td>" + dGroup.weightlb +
                "</td><td>" + dGroup.outputdrive + "</td></tr>";
            $(tableID + " tbody").append(newProductContent);
        }
    });
}

function getJsonData(jData, callback) {
    var thisObj = [];
    var jsonFile = "";
    if ($('body#deutsch').length) {
        jsonFile = "assets/js/productSpecs-de.json";
    }else {
        jsonFile = "assets/js/productSpecs.json";
    }

    $.getJSON(jsonFile, function(data) {
      var myType = jData[0];
      var mySeries = jData[1];
      for (var i = 0; i < data.length; i++) {
        if (data[i].type === myType && data[i].series === mySeries) {
          var toolData = data[i].tools;
          for (var j = 0; j < toolData.length; j++) {
            thisObj[j] = {
                model: toolData[j].Model,
                tminnm: toolData[j].Torque_Min_nm,
                tmaxnm: toolData[j].Torque_Max_nm,
                tminlb: toolData[j].Torque_Min_lb,
                tmaxlb: toolData[j].Torque_Max_lb,
                rpm: toolData[j].RPM,
                weightkg: toolData[j].Weight_kg,
                weightlb: toolData[j].Weight_lb,
                outputdrive: toolData[j].outputdrive,
                specSheet: toolData[j].Model + toolData[j].specSheet
            };
          }
        }
      }
      callback(thisObj);
    });
}

function viewHotSpot(e) {
    $(e).parent().find("label").each(function () {
        if ($(e).attr("data-position") == $(this).data("framenum")) {
            var $this = $(this);
            if ($this.hasClass("view-spot")) {
                $this.removeClass("view-spot");
            }
            $this.addClass("show-me");
            $this.css({ opacity: 1, display: "block" });
        }
    });
}
function hideHotSpot(e) {
    $(e).parent().find("label").each(function () {
        $(this).removeClass("show-me");
        $(this).css( 'opacity', 0);
        /*if ($(this).has("video").length) {
            $("video", this).get(0).pause();
        }*/
        var thisModal = "#" + $(this).attr("data-spotname");
        if ($(thisModal).hasClass('view-spot')) {
            $(thisModal).removeClass("view-spot");
            setTimeout(function() {
                $(thisModal).remove();
            },2000);
        }else {
            $(thisModal).remove();
        }
    });
}
function makeActive(d) {
    var thisID = $(d).attr("id");
    var navLink = $('.viewer-control a[href="#' + thisID + '"]').parent("li");
    if(!$(d).hasClass("active")) {
        //hideHotSpot(".product-sprite");
        $(".cd-product-viewer-wrapper").removeClass("active");
        $(".viewer-control li").removeClass("active");
        $(d).addClass("active");
        $(navLink).addClass("active");
    }
}

function buildPlayHotspot(toolName,spotName,spotTag,spotImg,callback) {
    //var offsetTop = $(".product-view").offset().top;
    //offsetTop = offsetTop + $("main").scrollTop();

    var hotspotPopup = '';

    if (spotImg == "yes") {

        console.log("is an image? " + spotImg + spotName);
      hotspotPopup = '<div id="' + spotName + '" class="hotspot-modal ' + spotName + '"><a href = "" class="close" > X</a>' + "<p>" + spotTag + "</p>";
      hotspotPopup = hotspotPopup + '<img class="non-video" src="360_assets/videos/' + spotName + '.jpg" alt="">';
    } else {
      hotspotPopup = '<div id="' + spotName + '" class="hotspot-modal ' + spotName + '"><a href = "" class="close" > X</a>' + "<p>" + spotTag + "</p>";
      if (spotName != "none") {
        hotspotPopup = hotspotPopup + '<video class="tool-video" id="rangleFloat" preload="none" poster="360_assets/videos/' + spotName + '.jpg" title=" ">' + '<source src="360_assets/videos/' + spotName + '.m4v" type="video/mp4" />' + '<source src="360_assets/videos/' + spotName + '.webm" type="video/webm" />' + '<object type="application/x-shockwave-flash" data="360_assets/videos/flashfox.swf" width="1280" height="720" style="position:relative;">' + '<param name="movie" value="360_assets/videos/flashfox.swf" />' + '<param name="allowFullScreen" value="true" />' + '<param name="flashVars" value="autoplay=false&controls=false&fullScreenEnabled=false&posterOnEnd=true&loop=false&poster=360_assets/videos/' + spotName + ".jpg&src=" + spotName + ".m4v/>" + '<embed src="360_assets/videos/flashfox.swf" width="1280" height="720" style="position:relative;" flashVars="autoplay=false&controls=false&fullScreenEnabled=false&posterOnEnd=true&loop=false&poster=360_assets/videos/' + spotName + ".jpg&src=" + spotName + '.m4v" allowFullScreen="true" wmode="transparent" type="application/x-shockwave-flash" pluginspage="http://www.adobe.com/go/getflashplayer_en"/>' + '<img alt="rangle-float_lite" src="360_assets/videos/' + spotName + '.jpg" style="position:absolute;left:0;" width="100%" title="Video playback is not supported by your browser" />' + "</object>" + '</video><span class="replay hide-me">replay</spam>';
      }
    }
    hotspotPopup = hotspotPopup + '</div >';
    $(hotspotPopup).appendTo(toolName);
    callback();
}

function hotspotLocation(name) {
    $(name).find("label").each(function() {
        var thisData = {
            xPos: $(this).attr("data-xpos"),
            yPos: $(this).attr("data-ypos"),
            framenum: $(this).attr("data-framenum"),
            hotspot: $(this).attr("data-spotname"),
            tagline: $(this).attr("data-tagline")
        };
        thisData.yPos = ((thisData.yPos - 25) / 700) * 100;
        thisData.xPos = ((thisData.xPos - 25) / 1140) * 100;
        var styles = {};
        var isTop = thisData.xPos;
        var isBottom = thisData.yPos;

        styles = { top: thisData.yPos + "%", left: thisData.xPos + "%", opacity: 0 };

        //buildPlayHotspot(name, thisData.hotspot, thisData.tagline);
        $(this).css(styles);
        /*if ($(name + " .product-sprite").attr("data-position") === thisData.framnum ) {
            styles.opacity = 1;
            //$(this).addClass("show-me");
            $(this).css(styles);
        }else {
            $(this).css(styles);
            if (isTop < 55 && isTop > 45) {
                if (isBottom < 45) {
                    $(this).addClass("bottom");
                }else {
                    $(this).addClass("top");
                }
            }else {
                if (typeof styles.right != "undefined") {
                    $(this).addClass("right");
                }
            }
        }*/
    });
}
