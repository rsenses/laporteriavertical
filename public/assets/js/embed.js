$(document).ready(function() {
  if ($(".embed").length) {
    $(".embed").each(function(i, el) {
      console.log($(this));
      var player_id = $(this).attr("id");

      var autoplay = false;

      if (i === 0) {
        autoplay = true;
      }

      var options = {
        id: $(this).data("id"),
        width: 990,
        loop: true,
        autoplay: autoplay,
        mute: true,
        player_id: player_id
      };

      var player = new Vimeo.Player(player_id, options);

      player.setVolume(0);

      player.on("play", function() {
        console.log("played the video!");
      });

      trackPlayer(player);
    });
  }
});

$(".videoWrapper .cover").click(function() {
  $(this).addClass("close");

  var player_id = $(this)
    .next(".embed")
    .attr("id");

  var options = {
    id: $(this)
      .next(".embed")
      .data("id"),
    width: 990,
    loop: true,
    autoplay: true,
    mute: true,
    player_id: player_id
  };

  var player = new Vimeo.Player(player_id, options);

  player.ready().then(function() {
    player.play();
    player.setVolume(1);
  });
});

function fireEvent(action, id, title) {
  if (id == null || title == null) {
    return;
  }
  var category = "Video";
  var label = title + " | " + id;

  if (window.gtag) {
    window.gtag("event", action, {
      event_category: category,
      event_label: label
    });
  } else if (window.ga) {
    window.ga("send", "event", category, action, label);
  } else if (window._gaq) {
    window._gaq.push(["_trackEvent", category, action, label]);
  } else if (window.dataLayer) {
    window.dataLayer.push({
      event: "vimeo",
      event_category: category,
      event_action: action,
      event_label: label
    });
  }
}

function trackPlayer(player) {
  var progress = 0;
  var id;
  var title;
  player.off("play");
  player.off("timeupdate");
  player.off("ended");
  player.off("emailcapture");
  player.getVideoId().then(function(v) {
    id = v;
    fireEvent("load", id, title);
  });
  player.getVideoTitle().then(function(v) {
    title = v;
    fireEvent("load", id, title);
  });
  player.on("play", function(data) {
    fireEvent("play", id, title);
    player.off("play");
  });
  player.on("ended", function() {
    fireEvent("progress - 100%", id, title);
  });
  player.on("emailcapture", function(data) {
    fireEvent("emailcapture", id, title);
  });
  player.on("timeupdate", function(data) {
    var prev_progress = progress;
    if (data.percent >= 0.25 && progress < 0.25) {
      progress = 0.25;
    } else if (data.percent >= 0.5 && progress < 0.5) {
      progress = 0.5;
    } else if (data.percent >= 0.75 && progress < 0.75) {
      progress = 0.75;
    }
    if (prev_progress !== progress) {
      fireEvent("progress - " + progress * 100 + "%", id, title);
    }
  });
}