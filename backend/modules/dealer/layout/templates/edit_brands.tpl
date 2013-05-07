{include:{$BACKEND_CORE_PATH}/layout/templates/head.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl}

<div class="pageTitle">
	<h2>{$lblBrands|ucfirst}: {$item.name}</h2>
</div>

{form:edit}
	<p>
		<label for="title">{$lblName|ucfirst}</label>
		{$txtName} {$txtNameError}
	</p>
	<div class="tabs">
		<ul>
			<li><a href="#tabContent">{$lblImage|ucfirst}</a></li>
			<li><a href="#tabSEO">{$lblSEO|ucfirst}</a></li>
		</ul>

		<div id="tabContent">
			<div class="options">
				<p>
					{option:item.image}
						<img src="{$FRONTEND_FILES_URL}/dealer/brands/128x128/{$item.image}" width="128" height="128" alt="" />
					{/option:item.image}
				</p>
				<p>
					<label for="image">{$lblImage|ucfirst}</label>
					{$fileImage} {$fileImageError}
					<span class="helpTxt">{$msgHelpAvatar}</span>
				</p>
			</div>
		</div>	
		<div id="tabSEO">
			{include:{$BACKEND_CORE_PATH}/layout/templates/seo.tpl}
		</div>
	</div>
	{option:showDealerDelete}
	<a href="{$var|geturl:'delete_brand'}&amp;id={$item.id}" data-message-id="confirmDelete" class="askConfirmation button linkButton icon iconDelete">
		<span>{$lblDelete|ucfirst}</span>
	</a>
	<div id="confirmDelete" title="{$lblDelete|ucfirst}?" style="display: none;">
		<p>
			{$msgConfirmDelete|sprintf:{$item.name}}
		</p>
	</div>
	{/option:showDealerDelete}
	<div class="buttonHolderRight">
		<input id="editButton" class="inputButton button mainButton" type="submit" name="edit" value="{$lblEdit|ucfirst}" />
	</div>
{/form:edit}

{include:{$BACKEND_CORE_PATH}/layout/templates/structure_end_module.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/footer.tpl}