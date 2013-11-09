/**
* Core JavaScript routines for administrative menu pages.
*
* This is the development version of the code.
* Which ultimately produces menu-pages-min.js.
*
* This file is included with all WordPress themes/plugins by WebSharks, Inc.
*
* Copyright: © 2009-2011
* {@link http://www.websharks-inc.com/ WebSharks, Inc.}
* (coded in the USA)
*
* Released under the terms of the GNU General Public License.
* You should have received a copy of the GNU General Public License,
* along with this software. In the main directory, see: /licensing/
* If not, see: {@link http://www.gnu.org/licenses/}.
*
* @package WebSharks\Menu Pages
* @since x.xx
*/
jQuery(document).ready (function($)
	{
		$(window).resize ( /* Global function. */tb_position /* Thickbox resizer/positioner. */ = function()
			{
				var w = ($(window).width () > 720) ? 720 : $(window).width (), h = $(window).height (), admin_bar_h = ($('body.admin-bar').length) ? 28 : 0;
				$('#TB_window').css ({'width': w - 50 + 'px', 'height': h - 45 - admin_bar_h + 'px', 'top': 25 + admin_bar_h + 'px', 'margin-top': 0, 'margin-left': '-' + parseInt(((w - 50) / 2), 10) + 'px'});
				$('#TB_ajaxContent').css ({'width': w - 50 + 'px', 'height': h - 75 - admin_bar_h + 'px', 'margin': 0, 'padding': 0});
			});

		var $rs = $('table.ws-menu-page-table td.ws-menu-page-table-r'),
			$rsToggler = $('> .toggler', $rs), $rsWrapper = $('> .wrapper', $rs);

		var $groups = /* Query groups. */ $('div.ws-menu-page-group');
		$groups.each ( /* Go through each group, one at a time. */function(index)
			{
				var $this = $(this), $group = $this, title = $.trim ($group.attr ('title'));

				var $header = $('<div class="ws-menu-page-group-header">' + title + '</div>');

				$header.css /* Stack them sequentially, top to bottom. */ ({'z-index': 1});

				$header.insertBefore ($group), $header.click (function()
					{
						var $this = $(this), $group = $this.next ();

						if ($this.hasClass('open'))
							$this.add($group).removeClass ('open');
						else $this.add($group).addClass ('open');

						return /* Return. */ false;
					});
				if ($groups.length > 1 && index === 0)
					{
						$('<div class="ws-menu-page-groups-hide"><i class="fa fa-chevron-up"></i></div>')
							.insertBefore ('div.ws-menu-page-toolbox').click (function()
							{
								$('div.ws-menu-page-group-header').each (function()
									{
										var $this = $(this), $group = $this.next ();

										$this.add($group).removeClass ('open');

										return; // Return.
									});
								return /* Return. */ false;
							});
						$('<div class="ws-menu-page-groups-show"><i class="fa fa-chevron-down"></i></div>')
							.insertBefore ('div.ws-menu-page-toolbox').click (function()
							{
								$('div.ws-menu-page-group-header').each (function()
									{
										var $this = $(this), $group = $this.next ();

										$this.add($group).addClass ('open');

										return; // Return.
									});
								return /* Return. */ false;
							});
					}
				if ($group.attr ('default-state') === 'open')
					$header.trigger ('click');

				return; // Return.
			});
		$rsToggler.click(function()
		 {
			if($rs.hasClass('open'))
					$rsWrapper.hide(), $rs.removeClass('open');
			else // Open the sidebar (right-side).
					$rsWrapper.show(), $rs.addClass('open');
		});
		if($rsToggler.attr('default-state') === 'open') $rsToggler.click();

		$('div.ws-menu-page-r-group-header').click (function()
			{
				var $this = $(this), $group = $this.next ();

				if ($this.hasClass('open'))
					$this.add($group).removeClass ('open');
				else $this.add($group).addClass ('open');

				return /* Return. */ false;
			});
		$('div.ws-menu-page-group-header:first, div.ws-menu-page-r-group-header:first').css ({'margin-top': '0'});
		$('div.ws-menu-page-group-header:last, div.ws-menu-page-r-group-header:last, div.ws-menu-page-group:last').css ({'margin-bottom': '0'});

		$('input.ws-menu-page-media-btn').filter ( /* Only those that have a rel attribute. */function()
			{
				return /* Must have rel targeting an input id. */ ($(this).attr ('rel')) ? true : false;
			})
		.click ( /* Attach click events to media buttons with send_to_editor(). */function()
			{
				var $this = /* Record a reference to the media button here. */ $(this);

				window.send_to_editor = /* Works with Thickbox. */ function(html)
					{
						var $inp, $txt, rel = $.trim ($this.attr ('rel'));

						if /* An input field? */ (rel && ($inp = $('input#' + rel)).length > 0)
							{
								var oBg = $inp.css ('background-color'), src = $.trim ($(html).attr ('src'));
								src = (!src) ? $.trim ($('img', html).attr ('src')) : src;

								$inp.val (src), $inp.css ({'background-color': '#FFFFCC'}), setTimeout(function()
									{
										$inp.css ({'background-color': oBg});
									}, 2000);

								tb_remove /* Close. */ ();

								return; // Return.
							}
						else if /* Textarea? */ (rel && ($txt = $('textarea#' + rel)).length > 0)
							{
								var oBg = $txt.css ('background-color'), src = $.trim ($(html).attr ('src'));
								src = (!src) ? $.trim ($('img', html).attr ('src')) : src;

								$txt.val ($.trim ($txt.val ()) + '\n' + src), $txt.css ({'background-color': '#FFFFCC'}), setTimeout(function()
									{
										$txt.css ({'background-color': oBg});
									}, 2000);

								tb_remove /* Close. */ ();

								return; // Return.
							}
					};
				tb_show('', './media-upload.php?type=image&TB_iframe=true');

				return /* Return. */ false;
			});
		$('form#ws-updates-form').submit (function()
			{
				var errors = /* Intialize string of errors. */ '';

				if (!$.trim ($('input#ws-updates-fname').val ()))
					errors += 'First Name missing, please try again.\n\n';

				if (!$.trim ($('input#ws-updates-lname').val ()))
					errors += 'Last Name missing, please try again.\n\n';

				if (!$.trim ($('input#ws-updates-email').val ()))
					errors += 'Email missing, please try again.\n\n';

				else if (!$('input#ws-updates-email').val ().match (/^([a-z_~0-9\+\-]+)(((\.?)([a-z_~0-9\+\-]+))*)(@)([a-z0-9]+)(((-*)([a-z0-9]+))*)(((\.)([a-z0-9]+)(((-*)([a-z0-9]+))*))*)(\.)([a-z]{2,6})$/i))
					errors += 'Invalid email address, please try again.\n\n';

				if (errors = $.trim (errors))
					{
						alert('— Oops, you missed something: —\n\n' + errors);

						return false;
					}
				return true;
			});
	});