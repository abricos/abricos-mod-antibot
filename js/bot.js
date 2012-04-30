/*
@version $Id: opermove.js 1399 2012-02-01 07:18:22Z roosit $
@package Abricos
@license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

var Component = new Brick.Component();
Component.requires = { 
	mod:[
		{name: 'sys', files: ['container.js']}
	]
};
Component.entryPoint = function(NS){

	var Dom = YAHOO.util.Dom,
		E = YAHOO.util.Event,
		L = YAHOO.lang;

	var buildTemplate = this.buildTemplate;
	
	var BotEditorPanel = function(userid, callback){
		this.userid = userid;
		this.callback = callback;
		
		BotEditorPanel.superclass.constructor.call(this, {
			'width': '790px'
		});
	};
	YAHOO.extend(BotEditorPanel, Brick.widget.Dialog, {
		initTemplate: function(){
			return buildTemplate(this, 'panel,utable,urow,urowwait').replace('panel');
		},
		destroy: function(){
			BotEditorPanel.superclass.destroy.call(this);
		},
		onLoad: function(){
			var userid = this.userid;
			
			var TM = this._TM;
			TM.getEl('panel.table').innerHTML = TM.replace('utable', {
				'rows': TM.replace('urowwait')
			});

			var __self = this;
			Brick.ajax('antibot', {
				'data': {'do': 'user','userid': userid},
				'event': function(request){
					__self.renderUser(request.data);
				}
			});
		},
		onClick: function(el){
			var tp = this._TId['panel']; 
			switch(el.id){
			case tp['bclose']: this.close(); return true;
			}
		},
		renderUser: function(d){
			if (L.isNull(d)){ return; }
			Brick.console(d);
			var TM = this._TM, lst = "", userid = this.userid, 
				user = null, cnt = 0;
			
			for (var i=0;i<d['users'].length;i++){
				var u = d['users'][i];
				if (u['id'] == userid){
					user = u;
					continue;
				}
				cnt++;
				lst += TM.replace('urow', {
					'id': u['id'],
					'unm': u['unm'],
					'eml': u['eml'],
	    			'jd': Brick.dateExt.convert(u['jd']),
	    			'lv': Brick.dateExt.convert(u['lv'])
				});
			}
			
			TM.getEl('panel.table').innerHTML = TM.replace('utable', {
				'rows': lst
			});
			if (!L.isNull(user)){
				TM.getEl('panel.unm').innerHTML = user['unm'];
			}
			TM.getEl('panel.cnt').innerHTML = cnt;
		}
	});
	
	NS.BotEditorPanel = BotEditorPanel;
};