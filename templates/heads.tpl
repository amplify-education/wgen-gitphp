{*
 *  heads.tpl
 *  gitphp: A PHP git repository browser
 *  Component: Head view template
 *
 *  Copyright (C) 2009 Christopher Han <xiphux@gmail.com>
 *}

 {include file='header.tpl'}

 {* Nav *}
 <div class="page_nav">
   {include file='project_header.tpl' unselect='heads' commit=$head}
   <br /><br />
 </div>

 {include file='title.tpl' target='summary'}
 
  <table cellspacing="0">
    {include file='head_list.tpl' headlist=$headlist}
  </table>

 {include file='footer.tpl'}

