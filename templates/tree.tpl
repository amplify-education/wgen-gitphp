{*
 *  tree.tpl
 *  gitphp: A PHP git repository browser
 *  Component: Tree view template
 *
 *  Copyright (C) 2009 Christopher Han <xiphux@gmail.com>
 *}

 {include file='header.tpl'}

 {* Nav *}
   <div class="page_nav">
     {include file='project_header.tpl' unselect='tree' commit=$commit}
   </div>

 {include file='title.tpl' titlecommit=$commit}

 {include file='path.tpl' pathobject=$tree target='tree'}
 
 <div class="page_body">
   {* List files *}
   <table cellspacing="0">
     {foreach from=$tree->GetContents() item=treeitem}
       <tr class="{cycle values="light,dark"}">
         <td class="monospace">{$treeitem->GetModeString()}</td>
         {if $treeitem instanceof GitPHP_Blob}
	   <td>{$treeitem->GetSize()}</td>
           <td class="list">
             <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=blob&h={$treeitem->GetHash()}&hb={$commit->GetHash()}&f={$treeitem->GetPath()}" class="list">{$treeitem->GetName()}</a>
	   </td>
           <td class="link">
	     <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=blob&h={$treeitem->GetHash()}&hb={$commit->GetHash()}&f={$treeitem->GetPath()}">blob</a> | <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=history&h={$commit->GetHash()}&f={$treeitem->GetPath()}">history</a>
	   </td>
         {elseif $treeitem instanceof GitPHP_Tree}
	   <td></td>
           <td class="list">
             <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=tree&h={$treeitem->GetHash()}&hb={$commit->GetHash()}&f={$treeitem->GetPath()}">{$treeitem->GetName()}</a>
	   </td>
           <td class="link">
	     <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=tree&h={$treeitem->GetHash()}&hb={$commit->GetHash()}&f={$treeitem->GetPath()}">tree</a> | <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=snapshot&h={$treeitem->GetHash()}&f={$treeitem->GetPath()}">snapshot</a>
	   </td>
         {/if}
       </tr>
     {/foreach}
   </table>
 </div>

 {include file='footer.tpl'}

