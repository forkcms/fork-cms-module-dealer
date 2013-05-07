{include:{$BACKEND_CORE_PATH}/layout/templates/head.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/structure_start_module.tpl}

<div class="pageTitle">
	<h2>{$lblDealer|ucfirst}: {$lblAdd}</h2>
</div>

{form:add}
	<p>
		<label for="title">{$lblName|ucfirst}</label>
		{$txtName} {$txtNameError}
	</p>

	<table border="0" cellspacing="0" cellpadding="0" width="100%">
		<tr>
			<td id="leftColumn">

				<div class="box">
					<div class="heading">
						<h3>{$lblBrands|ucfirst}</h3>
					</div>
					<div class="options">
						{option:type}
							<ul>
								{iteration:type}<li>{$type.chkType} <label for="{$type.id}">{$type.label|ucfirst}</label></li>{/iteration:type}
							</ul>
						{/option:type}
						{option:!type}
							{$msgNoBrandsFound}
						{/option:!type}
					</div>
				</div>
				
				<div class="box">
					<div class="heading">
						<h3>{$lblAddress|ucfirst}</h3>
					</div>
					
					<div class="options">
						<p>
							<label for="street">{$lblStreet|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
							{$txtStreet} {$txtStreetError}
						</p>
						<p>
							<label for="number">{$lblNumber|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
							{$txtNumber} {$txtNumberError}
						</p>
						<p>
							<label for="zip">{$lblZip|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
							{$txtZip} {$txtZipError}
						</p>
						<p>
							<label for="city">{$lblCity|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
							{$txtCity} {$txtCityError}
						</p>
						<p>
							<label for="country">{$lblCountry|ucfirst}<abbr title="{$lblRequiredField}">*</abbr></label>
							{$ddmCountry} {$ddmCountryError}
						</p>
					</div>
				</div>
			</td>

			<td id="sidebar">

				<div id="publishOptions" class="box">
					<div class="heading">
						<h3>{$lblStatus|ucfirst}</h3>
					</div>

					<div class="options">
						<ul class="inputList">
							{iteration:hidden}
								<li>
									{$hidden.rbtHidden}
									<label for="{$hidden.id}">{$hidden.label}</label>
								</li>
							{/iteration:hidden}
						</ul>
					</div>
				</div>
				
				<div class="box">
					<div class="heading">
						<h3>{$lblContact|ucfirst}</h3>
					</div>

					<div class="options">
						<p>
							<label for="tel">{$lblTel|ucfirst}</label>
							{$txtTel} {$txtTelError}
						</p>
						<p>
							<label for="fax">{$lblFax|ucfirst}</label>
							{$txtFax} {$txtFaxError}
						</p>
						<p>
							<label for="email">{$lblEmail|ucfirst}</label>
							{$txtEmail} {$txtEmailError}
						</p>
						<p>
							<label for="website">{$lblWebsite|ucfirst}</label>
							{$txtWebsite} {$txtWebsiteError}
						</p>
					</div>
				</div>
				
				<div class="box">
					<div class="heading">
						<h3>{$lblAvatar|ucfirst}</h3>
					</div>

					<div class="options">
						<p>
							{option:item.avatar}
								<img src="{$FRONTEND_FILES_URL}/frontend_dealer/avatars/128x128/{$item.avatar}" width="128" height="128" alt="" />
							{/option:item.avatar}
						</p>
						<p>
							<label for="avatar">{$lblAvatar|ucfirst}</label>
							{$fileAvatar} {$fileAvatarError}
							<span class="helpTxt">{$msgHelpAvatar}</span>
						</p>
					</div>
				</div>
			</td>
		</tr>
	</table>

	
	<div class="fullwidthOptions">
		<div class="buttonHolderRight">
			<input id="addButton" class="inputButton button mainButton" type="submit" name="add" value="{$lblPublish|ucfirst}" />
		</div>
	</div>
{/form:add}

{include:{$BACKEND_CORE_PATH}/layout/templates/structure_end_module.tpl}
{include:{$BACKEND_CORE_PATH}/layout/templates/footer.tpl}