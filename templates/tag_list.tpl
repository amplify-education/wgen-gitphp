{foreach from=$taglist item=tag}
  <tr class="{cycle name=tags values="light,dark"}">
    {assign var=object value=$tag->GetObject()}
    <td><em>{$object->GetAge()|agestring}</em></td>
    <td><a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a={$tag->GetType()}&h={$object->GetHash()}" class="list"><strong>{$tag->GetName()}</strong></a></td>
    <td>
      {assign var=comment value=$tag->GetComment()}
      {if count($comment) > 0}
        <a class="list" href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=tag&h={$tag->GetName()}">{$comment[0]}</a>
      {/if}
    </td>
    <td class="link">
      {if !$tag->LightTag()}
        <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=tag&h={$tag->GetName()}">tag</a> | 
      {/if}
      <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a={$tag->GetType()}&h={$tag->GetHash()}">{$tag->GetType()}</a>
      {if $tag->GetType() == "commit"}
        | <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=shortlog&h=refs/tags/{$tag->GetName()}">shortlog</a> | <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=log&h=refs/tags/{$tag->GetName()}">log</a> | <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=snapshot&h={$object->GetHash()}">snapshot</a>
        {if $mark != $object->GetHash()}
          | <a href="{$object->GetMarkUrl()}">Select for diff</a>
        {else}
          | <a href="{$object->GetMarkUrl(true)}">Unselect for diff</a>
        {/if}
        {if $mark && $mark != $object->GetHash()}
          | <a href="{$SCRIPT_NAME}?p={$project->GetProject()|urlencode}&a=commitdiff&h={$object->GetHash()}&hp={$mark}">Diff against selected (<abbr title="{$mark}">{$mark_abbr}</abbr>)</a>
        {/if}
      {/if}
    </td>
  </tr>
{/foreach}
