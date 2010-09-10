{foreach from=$taglist item=tag}
  <tr class="{cycle name=tags values="light,dark"}">
    {assign var=derefObject value=$tag->GetDereferencedObject()}
    {assign var=refObject value=$tag->GetObject()}
    {if $derefObject->GetType() == 'commit'}
      <td><em>{$derefObject->GetAge()|agestring}</em></td>
    {else}
      <td></td>
    {/if}
    <td>
      <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a={$derefObject->GetType()}&h={$derefObject->GetHash()}" class="list"><strong>{$tag->GetName()}</strong></a></td>
    <td>
    {if $refObject->GetType() == 'tag'}
      {assign var=comment value=$refObject->GetComment()}
      {if count($comment) > 0}
        <a class="list" href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=tag&h={$tag->GetName()}">{$comment[0]}</a>
      {/if}
    {/if}
    </td>
    <td class="link">
      {if $refObject->GetType() == "tag"}
         <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=tag&h={$tag->GetName()}">tag</a> | 
      {/if}
      <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a={$derefObject->GetType()}&h={$derefObject->GetHash()}">{$derefObject->GetType()}</a>
      {if $derefObject->GetType() == "commit"}
        | <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=shortlog&h=refs/tags/{$tag->GetName()}">shortlog</a> | <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=log&h=refs/tags/{$tag->GetName()}">log</a> | <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=snapshot&h={$derefObject->GetHash()}">snapshot</a>
        {if $mark != $derefObject->GetHash()}
          | <a href="{$derefObject->GetMarkUrl()}">Select for diff</a>
        {else}
          | <a href="{$derefObject->GetMarkUrl(true)}">Unselect for diff</a>
        {/if}
        {if $mark && $mark != $derefObject->GetHash()}
          | <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a={$derefObject->GetType()}diff&h={$derefObject->GetHash()}&hp={$mark}">Diff against selected (<abbr title="{$mark}">{$mark_abbr}</abbr>)</a>
        {/if}
      {/if}
    </td>
  </tr>
{/foreach}

