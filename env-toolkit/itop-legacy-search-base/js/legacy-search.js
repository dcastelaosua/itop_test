/*
 * Copyright (C) 2012-2018 Combodo SARL
 *
 * This file is part of iTop.
 *
 * iTop is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * iTop is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with iTop. If not, see <http://www.gnu.org/licenses/>
 */

function FixSearchFormsDisposition()
{
    // Fix search forms
    $('.SearchDrawer').each(function() {
        var colWidth = 0;
        var labelWidth = 0;
        $('label:visible', $(this)).each( function() {
            var l = $(this).parent().width() - $(this).width();
            colWidth = Math.max(l, colWidth);
            labelWidth = Math.max($(this).width(), labelWidth);
        });
        $('label:visible', $(this)).each( function() {
            if($(this).data('resized') != true)
            {
                $(this).parent().width(colWidth + labelWidth);
                $(this).width(labelWidth).css({display: 'inline-block'}).data('resized', true);
            }
        });
    });

}