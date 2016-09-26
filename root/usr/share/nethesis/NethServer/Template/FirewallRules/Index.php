<?php
/* @var $view \Nethgui\Renderer\Xhtml */

echo $view->header()->setAttribute('template', $T('Index_header'));

echo $view->buttonList()
    //->insert($view->button('ApplySelection', $view::BUTTON_SUBMIT))    
    ->insert($view->buttonList()
        ->setAttribute('class', 'Buttonset v1 inlineblock')
        ->insert($view->button('Create_last', $view::BUTTON_LINK))
        ->insert($view->button('Create_first', $view::BUTTON_LINK))
        ->insert($view->button('Configure', $view::BUTTON_LINK)->setAttribute('value', $view->getModuleUrl('../General')))
    )
    ->insert($view->button('Commit', $view::BUTTON_SUBMIT)->setAttribute('receiver', 'Commit'))
    ->insert($view->button('Help', $view::BUTTON_HELP))
;

echo $view->panel()->setAttribute('id', 'ShowGroup')
    ->insert($view->button('ShowRules',  $view::BUTTON_LINK))
    ->insert($view->button('ShowServices',  $view::BUTTON_LINK))
    ->insert($view->button('ShowRoutes',  $view::BUTTON_LINK))
    ->insert($view->button('ShowTrafficShaping',  $view::BUTTON_LINK))
;

$filterTarget = $view->getClientEventTarget('a');
echo $view->hidden('a');

echo $view->objectsCollection('Rules')
    ->setAttribute('placeholders', array('cssAction', 'ActionIcon', 'LogIcon', 'LogLabel', 'Src', 'Dst', 'SrcColor', 'DstColor', 'status', 'ExtraTags'))
    ->setAttribute('key', 'id')
    ->setAttribute('ifEmpty', function ($view) use ($T) {
        return $T('NoRulesDefined_label');
    })
    ->setAttribute('template', function ($view) use ($T) {
        return $view->panel()
            ->setAttribute('class', 'fwrule ${cssAction} ${status}')
            ->insert($view->hidden('metadata', $view::STATE_DISABLED))
            ->insert($view->textInput('Position', $view::LABEL_NONE))
            ->insert($view->panel()->setAttribute('class', 'actbox')
                ->insert($view->literal('<i class="fwicon fa ${ActionIcon}"></i> '))
                ->insert($view->textLabel('Action')->setAttribute('tag', 'span'))
                ->insert($view->literal('<div class="log"><i class="fwicon fwicon-log fa ${LogIcon} gray"></i>  <span class="gray">${LogLabel}</div>'))
                )
            ->insert($view->panel()->setAttribute('class', 'descbox')
                    ->insert($view->panel()->setAttribute('class', 'fields')
                        ->insert($view->textLabel('Src')->setAttribute('class', '${SrcColor}')->setAttribute('tag', 'div')->setAttribute('escapeHtml', FALSE))
                        ->insert($view->literal(' <div class="arrow fa">&#xf178;</div> '))
                        ->insert($view->textLabel('Dst')->setAttribute('class', '${DstColor}')->setAttribute('tag', 'div')->setAttribute('escapeHtml', FALSE))
                        ->insert($view->textLabel('ExtraTags')->setAttribute('escapeHtml', FALSE))
                    )
                ->insert($view->textLabel('Description')->setAttribute('tag', 'div')))
            ->insert($view->buttonList()->setAttribute('class', 'Buttonset v1')
                ->insert($view->button('Edit', $view::BUTTON_LINK))
                ->insert($view->button('Copy', $view::BUTTON_LINK))
                ->insert($view->button('Delete', $view::BUTTON_LINK))
            )
        ;
    });

echo $view->hidden('hasChanges');

$rulesId = $view->getUniqueId('Rules');
$actionId = $view->getUniqueId();
$commitId = $view->getUniqueId('Commit');
$deleteId = $view->getUniqueId('Delete');
$deleteUrl = $view->getModuleUrl('../Delete');
$hasChangesTarget = $view->getClientEventTarget('hasChanges');
$view->includeTranslations(array(
    'confirm_reload_label'
    ));

$ruleStep = \NethServer\Module\FirewallRules::RULESTEP;

$view->includeCss('
#ShowGroup { margin-bottom: 1em; border-bottom: 1px solid #d3d3d3; position: relative }
#ShowGroup a { position: relative; bottom: -1px; margin-right: .2em }

.fwrule {min-height: 50px; border:1px solid #d3d3d3; display: flex; margin-bottom: .5em; border-radius: 3px;}
.fwrule .Buttonset {flex-grow: 0; margin-right: 0}
.fwrule .Buttonset [role=button] {border-top: none}
.fwrule .actbox {padding: 3px 3px 3px 15px; min-width: 5.5em; text-transform: uppercase; cursor: move; font-size: 1.4em; font-weight: bold}
.fwrule .log { font-size: 0.8em; font-weight: normal }
.fwrule .fields {margin-bottom: 5px; font-size: 1.4em}
.fields .Src { display: inline-block; min-width: 10em }
.fields .Dst, .fields .arrow { display: inline-block; }
.fwrule .descbox {flex-grow: 8; border-left: 1px solid #d3d3d3; padding: 3px 3px 3px 1ex; position: relative }
.fwrule .Description { bottom: 3px; position: absolute }
.fwrule.high .fwicon { color: blue }
.fwrule.low .fwicon { color: red }
.fwrule.disabled {color: gray !important; background-color: #eee}
.fwrule.disabled .actbox, .fwrule.disabled .actbox .fwicon, .fwrule.disabled .fields, .fwrule.disabled .TextLabel i.fa {color: gray !important}
.placeholder {background-color: yellow; margin-bottom: 1.5em; background: linear-gradient(to bottom, rgba(234,239,181,1) 0%,rgba(225,233,160,1) 100%);}

.drop .actbox { color: red }
.reject .actbox { color: #700000}
.accept .actbox { color: green}

.green i.fa {color: green}
.red i.fa {color: red}
.orange i.fa {color: orange}
.blue i.fa {color: blue}

.my-state-active {
    background: #fff;
    color: #00729D;
    border-color: #00729D;
    border-bottom-color: #fff;
}

.fwrule.sortable .actbox {
    background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAgAAAAQCAYAAAArij59AAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3wsYDwA1kC7JJQAAACBJREFUKM9jXrJkyX9WVtbGq1evMmBjMzEQAKMKhpMCAAwjD5FRISjaAAAAAElFTkSuQmCC);
    background-position: 2px 50%;
    background-repeat: no-repeat
}

.unsortable .Button.Copy, .unsortable .Button.Delete {
    display: none;
}

.fwrule.unsortable .actbox { cursor: inherit };
');

$view->includeJavascript("
jQuery(function ($) {
    $(window).on('unload beforeunload', function(e) {
       if($('input.${hasChangesTarget}').val() == '1') {
            return $.Nethgui.T('confirm_reload_label');
       }
    });

    $('#${rulesId}').sortable({
        axis: 'y',
        handle: '.actbox',
        cancel: '.unsortable',
        items: '> .sortable',
        placeholder: 'placeholder',
        opacity: 0.6,        
        forcePlaceholderSize: true,
        update: function(e, ui) {            
            var prev = Number(ui.item.prev().find('input.Position').val());
            var next = Number(ui.item.next().find('input.Position').val());

            if( ! prev) {
                prev = 0;
            }
            if( ! next) {
                next = prev + 2 * $ruleStep;
            }

            var newpos = prev + Math.floor((next - prev) / 2);          
            ui.item.find('input.Position').val(newpos);            

            var formElement = $('#${actionId}').find('form');
            $.Nethgui.Server.ajaxMessage({
                isMutation: true,
                url: formElement.prop('action') + '/sortonly',
                data: formElement.serialize(),
                freezeElement: $(this)
            });
        }
    });
    var style = '<style type=\"text/css\">.Position {display: none}</style>';
    $('head').append(style);
    
    $('input.${hasChangesTarget}').on('nethguiupdateview', function (e, val) {
        $('#${commitId}').trigger(val === '1' ? 'nethguienable' : 'nethguidisable');
    }).on('nethguicreate', function () {
        var val = $(this).attr('value');
        window.setTimeout(function() {
            $('#${commitId}').trigger(val === '1' ? 'nethguienable' : 'nethguidisable');
        }, 100);
    });

    var updateShowGroup = function (e, value) {
        var re = RegExp('=' + value + '$');
        $('#ShowGroup').children('a').each(function(index, elem) {
            if(re.test($(elem).attr('href'))) {
                $(elem).addClass('my-state-active');
            } else {
                $(elem).removeClass('my-state-active');
            }
        });
    };

    $('.${filterTarget}').on('nethguiupdateview', updateShowGroup);
    updateShowGroup(null, 'rules');
});
" . '
/*!
 * jQuery UI Touch Punch 0.2.3
 *
 * Copyright 2011–2014, Dave Furfero
 * Dual licensed under the MIT or GPL Version 2 licenses.
 *
 * Depends:
 *  jquery.ui.widget.js
 *  jquery.ui.mouse.js
 */
!function(a){function f(a,b){if(!(a.originalEvent.touches.length>1)){a.preventDefault();var c=a.originalEvent.changedTouches[0],d=document.createEvent("MouseEvents");d.initMouseEvent(b,!0,!0,window,1,c.screenX,c.screenY,c.clientX,c.clientY,!1,!1,!1,!1,0,null),a.target.dispatchEvent(d)}}if(a.support.touch="ontouchend"in document,a.support.touch){var e,b=a.ui.mouse.prototype,c=b._mouseInit,d=b._mouseDestroy;b._touchStart=function(a){var b=this;!e&&b._mouseCapture(a.originalEvent.changedTouches[0])&&(e=!0,b._touchMoved=!1,f(a,"mouseover"),f(a,"mousemove"),f(a,"mousedown"))},b._touchMove=function(a){e&&(this._touchMoved=!0,f(a,"mousemove"))},b._touchEnd=function(a){e&&(f(a,"mouseup"),f(a,"mouseout"),this._touchMoved||f(a,"click"),e=!1)},b._mouseInit=function(){var b=this;b.element.bind({touchstart:a.proxy(b,"_touchStart"),touchmove:a.proxy(b,"_touchMove"),touchend:a.proxy(b,"_touchEnd")}),c.call(b)},b._mouseDestroy=function(){var b=this;b.element.unbind({touchstart:a.proxy(b,"_touchStart"),touchmove:a.proxy(b,"_touchMove"),touchend:a.proxy(b,"_touchEnd")}),d.call(b)}}}(jQuery);
');

