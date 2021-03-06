Ext.define('GS.view.Login',{
   extend: 'Ext.form.Panel',
   requires: [
      'Ext.form.FieldSet',
      'Ext.form.Password'
   ],
//   initialize: function () {
//       this.callParent(arguments);
//       var appurl = document.URL;
//       appurl = appurl.replace("plugins/monitoring/m/", "");
//       Ext.getCmp('serverurl').setValue(appurl);
//   },
   xtype: 'LoginForm',
   id: 'LoginForm',

   config: {
      title: 'Login',
      iconCls: 'home',


      items: [
         {
         xtype: 'fieldset',
         defaults:
            {
            labelWidth: '32%'
            },
         instructions: '(Please provide all credentials to login)',
         layout: {
            type: 'vbox'
         },
         items:
            [
               {
                  xtype: "toolbar",
                  docked: "top",
                  title: "Login to GLPI - Monitoring"
               },
               {
                  xtype: 'textfield',
                  id: 'username',
                  name : 'userName',
                  label: 'Username',
                  value: 'glpi',
                  allowBlank:false
               },
               {
                  xtype: 'passwordfield',
                  id: 'password',
                  name : 'password',
                  label: 'Password',
                  value: 'glpi'
//               },
//               {
//                  xtype: 'textfield',
//                  id: 'serverurl',
//                  name : 'server',
//                  label: 'Server'
//                  //value: 'http://192.168.20.194/glpi083/'
//                  //value:   }
               },
               {
                  xtype: 'button',
                  ui: 'action',
                  text: 'Login',            
                  action: 'submitLogin',
                  docked: 'bottom'
               }
            ]//END ITEMS
         }
      ]
   }//END CONFIG
}); 

