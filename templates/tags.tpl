{*
 *  tags.tpl
 *  gitphp: A PHP git repository browser
 *  Component: Tag view template
 *
 *  Copyright (C) 2009 Christopher Han <xiphux@gmail.com>
 *}

{include file='header.tpl'}

{* Nav *}
<div class="page_nav">
  {include file='project_header.tpl' unselect='tags' commit=$head}
  <br /><br />
</div>

{include file='title.tpl' target='summary'}

{* Display tags *}
  <table cellspacing="0">
    {include file='tag_list.tpl' taglist=$taglist}
  </table>

{include file='footer.tpl'}

