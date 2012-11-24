(function($) {
	
	var methods = {
		
		init: function() {
			return this.each(function() {
				var $this = $(this);
				if ($this.hasClass("simpleTreeMenu") === false) {
					$this.hide();
					$(this).addClass("simpleTreeMenu");
					$this.children("li").each(function() {
						methods.buildNode($(this));
					});
					if (private.hasLocalStorage() === true) {
						state = localStorage.getItem(
							private.localStorageKey.apply(this)
						);
						if (state != null) {
							state = state.split(",");
							if (state.length > 0) {
								methods.deserialize.call(this, state);
							}
						}
					}
					$(this).show();
				}
			});
		},
		
		buildNode: function($li) {
			if ($li.children("ul").length > 0) {
				$li.children("ul").hide();
				$li.addClass("Node").click(function(event) {
					var $t = $(this);
					if ($t.hasClass("expanded")) {
						$t.removeClass("expanded").children("ul").hide();
					}
					else {
						$t.addClass("expanded").children("ul").show();
					}
					event.stopPropagation();
				});
				if ($li.children("ul").children("li").length == 0) {
					$li.addClass("EmptyNode");
				}
				$li.children("ul").children("li").each(function() {
					methods.buildNode($(this));
				});
			} else {
				$li.addClass("Leaf").click(function(event) {
					event.stopPropagation();
				});
				return;
			}
		},
		
		serialize: function() {
			state = [];
			$('.Node, .Leaf', $(this)).each(function() {
				var s = $(this).hasClass("expanded") ? 
					private.EXPANDED : private.COLLAPSED;
				state.push(s);
			});
			if (private.hasLocalStorage() === true) {
				localStorage.setItem(
					private.localStorageKey.apply(this), state.join()
				);
			}
		},
		
		deserialize: function(state) {
			$('.Node, .Leaf', $(this)).each(function(index) {
				if (state[index] == private.EXPANDED) {
					$(this).addClass("expanded").children("ul").show();
				}
			});
		},
		
		expandToNode: function($li) {
			if ($li.parent().hasClass("simpleTreeMenu")) {
				if (!$li.hasClass("expanded")) {
					$li.addClass("expanded").children("ul").show();
				}
			}
			$li.parents("li", "ul.simpleTreeMenu").each(function() {
				var $t = $(this);
				if (!$t.hasClass("expanded")) {
					$t.addClass("expanded").children("ul").show();
				}
			});
		},
		
		searchForUrl: function() {
			$('.Leaf', $(this)).each(function() {
				$leaf = $(this);
				current_url = document.location.pathname.slice(1);
				if ($leaf.find('a').attr('href').indexOf(current_url) >= 0) {
					methods.expandToNode($leaf);
					$leaf.addClass('selected');
				}
			});
		},
		
		expandAll: function() {
			$(this).find("li.Node").each(function() {
				$t = $(this);
				if (!$t.hasClass("expanded")) {
					$t.addClass("expanded").children("ul").show();
				}
			});
		},
		
		closeAll: function() {
			$("ul", $(this)).hide();
			var $li = $("li.Node");
			if ($li.hasClass("expanded")) {
				$li.removeClass("expanded");
			}
		},	
	};
	
	var private = {
		
		EXPANDED: "expanded",
		COLLAPSED: "collapsed",
		localStorageKeyPrefix: "jQuery-simpleTreeMenu-treeState-",
		
		hasLocalStorage: function() {
			return (
				localStorage && localStorage.setItem && localStorage.getItem
			);
		},
		
		localStorageKey: function() {
			return private.localStorageKeyPrefix + $(this).attr("id");
		}
		
	};
	
	$.fn.simpleTreeMenu = function(method) {
		if (methods[method]) {
			return methods[method].apply(
				this, Array.prototype.slice.call(arguments, 1)
			);
		}
		else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
	}
		else {
			$.error(
				'Method ' +  method + ' does not exist on jQuery.simpleTreeMenu'
			);
		}
	};
	
})(jQuery);

