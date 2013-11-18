/** @param {jQuery} $ jQuery Object */
!function($, window, document, _undefined) {
	XenForo.ReputationInjector = function($div) { this.__construct($div); };
	XenForo.ReputationInjector.prototype = {
		__construct: function($div) {
			this.$div = $div;
			this.configuration = ReputationInjectorConfiguration;
			this.$CallForAction = $div.find('.CallForAction');
			this.$DisplayOnly = $div.find('.DisplayOnly');
			
			this.regex = {};
			this.regex.post_id = this.buildRegExp(this.configuration.placeholder.postId);
			
			console.log(this.configuration);
			
			var post;
			for (i in this.configuration.data.posts) {
				post = this.configuration.data.posts[i];

				this.initPost(post);
			}
		}
	
		,buildRegExp: function(placeholder) {
			var str = '';
			
			for (var i = 0; i < placeholder.length; i++) {
				str += placeholder[i];
			}
			
			return new RegExp(str);
		}
	
		,initPost: function(post) {
			var template = '';
			if (post.bdReputation_canGive && post.bdRepubation_given == 0) {
				template = this.$CallForAction.html();
			}			
			if (template.length == 0 && post.bdReputation_canView) {
				template = this.$DisplayOnly.html();
			}
			if (template.length == 0) { 
				return false;
			}
			
			template = this.prepareTemplate(template, post);
			
			var $target = $('#post-' + post.post_id + ' .privateControls');
			
			$('<span class="ReputationInjection">' + template + '</span>').xfInsert('appendTo', $target).css('display', 'inline');
			
			return true;
		}
		
		,prepareTemplate: function(template, post) {
			template = template.replace(this.regex.post_id, post.post_id);
			
			return template;
		}
	};

	XenForo.register('div.ReputationInjector', 'XenForo.ReputationInjector');

}
(jQuery, this, document);