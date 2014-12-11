/*
 @package Abricos
 @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

var Component = new Brick.Component();
Component.requires = {
    mod: [
        {name: 'sys', files: ['container.js']}
    ]
};
Component.entryPoint = function(NS){

    var Dom = YAHOO.util.Dom,
        E = YAHOO.util.Event,
        L = YAHOO.lang;

    var buildTemplate = this.buildTemplate;

    var StopSpamPanel = function(callback){
        this.callback = L.isFunction(callback) ? callback : function(){
        };

        StopSpamPanel.superclass.constructor.call(this, {
            'width': '790px'
        });
    };
    YAHOO.extend(StopSpamPanel, Brick.widget.Dialog, {
        initTemplate: function(){
            return buildTemplate(this, 'panel,utable,urow,urowwait').replace('panel');
        },
        destroy: function(){
            StopSpamPanel.superclass.destroy.call(this);
        },
        onLoad: function(){
            var TM = this._TM;
            TM.getEl('panel.table').innerHTML = TM.replace('utable', {
                'rows': TM.replace('urowwait')
            });

            this.users = [];

            var __self = this;
            Brick.ajax('antibot', {
                'data': {'do': 'stopspam'},
                'event': function(request){
                    __self.renderUser(request.data);
                }
            });
        },
        onClick: function(el){
            var tp = this._TId['panel'];
            switch (el.id) {
                case tp['btoadd']:
                    this.botToAppend();
                    return true;
                case tp['bcancel']:
                    this.botToCancel();
                    return true;
                case tp['badd']:
                    this.botAppend();
                    return true;
                case tp['bclose']:
                    this.close();
                    return true;
            }
        },
        renderUser: function(d){
            if (L.isNull(d)){
                return;
            }

            var TM = this._TM, lst = "", cnt = 0;

            this.users = d['users'];

            for (var i = 0; i < d['users'].length; i++){
                var u = d['users'][i];
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
            /*
             if (!L.isNull(user)){
             TM.getEl('panel.unm').innerHTML = user['unm'];
             }
             /**/
            // TM.getEl('panel.cnt').innerHTML = cnt;
        },
        botToAppend: function(){
            var TM = this._TM;
            Dom.setStyle(TM.getEl('panel.istobot'), 'display', '');
            Dom.setStyle(TM.getEl('panel.badd'), 'display', '');
            Dom.setStyle(TM.getEl('panel.bcancel'), 'display', '');
            Dom.setStyle(TM.getEl('panel.btoadd'), 'display', 'none');
        },
        botToCancel: function(){
            var TM = this._TM;
            Dom.setStyle(TM.getEl('panel.istobot'), 'display', 'none');
            Dom.setStyle(TM.getEl('panel.badd'), 'display', 'none');
            Dom.setStyle(TM.getEl('panel.bcancel'), 'display', 'none');
            Dom.setStyle(TM.getEl('panel.btoadd'), 'display', '');
        },
        botAppend: function(){
            var __self = this;

            var uids = [];
            for (var i = 0; i < this.users.length; i++){
                uids[uids.length] = this.users[i].id;
            }

            Brick.ajax('antibot', {
                'data': {'do': 'stopspamappend', 'uids': uids},
                'event': function(request){
                    __self.close();
                    __self.callback();
                }
            });
        }
    });

    NS.StopSpamPanel = StopSpamPanel;
};