{**
 * settingsForm.tpl
 *
 * Copyright (c) 2012 Aldi Alimucaj
 * Copyright (c) 2015 Felix Gründer
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * Print on Demand plugin settings
 *
 *}
{strip}
{assign var="pageTitle" value="plugins.generic.pod.displayName"}
{include file="common/header.tpl"}
{/strip}

<div id="customThemeSettings" class="podSettings">

	<form method="post" action="{plugin_url path="settings"}" enctype="multipart/form-data">
	{include file="common/formErrors.tpl"}

	<table width="100%" class="data">
		<tr valign="top">
			<td width="20%" class="label">{fieldLabel name="activated" required="true" key="plugins.generic.pod.activated"}</td>
			<td width="30%" class="value">
				<select name="activated">
					<option value="1" {if $activated=="1"}selected{/if}>{translate key="common.yes"}</option>
					<option value="0" {if $activated=="0"}selected{/if}>{translate key="common.no"}</option>
				</select>
			</td>
			<td width="50%" class="podSettingsInfo">{translate key="plugins.generic.pod.activatedHelp"}</td>
		</tr>

		<tr valign="top">
			<td width="20%" class="label">{fieldLabel name="partner" required="true" key="plugins.generic.pod.partner"}</td>
			<td width="30%" class="value">
				<input name="partner" type="text" id="partner" size="40"  value="{$partner|escape}" {if $partner} {/if}/>
			</td>
			<td width="50%" class="podSettingsInfo">{translate key="plugins.generic.pod.partnerHelp"}</td>
		</tr>
		<tr valign="top">
			<td width="20%" class="label">{fieldLabel name="podSecretKey" required="true" key="plugins.generic.pod.podSecretKey"}</td>
			<td width="30%" class="value">
				<input name="podSecretKey" type="text" id="podSecretKey" size="40"  value="{$podSecretKey|escape}" {if $podSecretKey} {/if}/>
			</td>
			<td width="50%" class="podSettingsInfo">{translate key="plugins.generic.pod.podSecretKeyHelp"}</td>
		</tr>
		<tr valign="top">
			<td width="20%" class="label">{fieldLabel name="mandant" required="true" key="plugins.generic.pod.mandant"}</td>
			<td width="30%" class="value">
				<input name="mandant" type="text" id="mandant" size="40"  value="{$mandant|escape}" {if $mandant} {/if}/>
			</td>
			<td width="50%" class="podSettingsInfo">{translate key="plugins.generic.pod.mandantHelp"}</td>
		</tr>
		<tr valign="top">
			<td width="20%" class="label">{fieldLabel name="action" required="true" key="plugins.generic.pod.action"}</td>
			<td width="30%" class="value">
				<input name="action" type="text" id="action" size="40"  value="{$action|escape}"/>
			</td>
			<td width="50%" class="podSettingsInfo">{translate key="plugins.generic.pod.actionHelp"}</td>
		</tr>
		<tr valign="top">
			<td width="20%" class="label">{fieldLabel name="repository" required="true" key="plugins.generic.pod.repository"}</td>
			<td width="30%" class="value">
				<input name="repository" type="text" id="repository" size="40"  value="{$repository|escape}"/>
			</td>
			<td width="50%" class="podSettingsInfo">{translate key="plugins.generic.pod.repositoryHelp"}</td>
		</tr>
		<tr valign="top">
			<td width="20%" class="label">{fieldLabel name="authors"  key="plugins.generic.pod.authors"}</td>
			<td width="30%" class="value">
				<input name="authors" type="text" id="authors" size="40"  value="{$authors|escape}" />
			</td>
			<td width="50%" class="podSettingsInfo">{translate key="plugins.generic.pod.authorsHelp"}</td>
		</tr>
		<tr valign="top">
			<td width="20%" class="label">{fieldLabel name="content_bw"  key="plugins.generic.pod.colored"}</td>
			<td width="30%" class="value">
				<input name="content_bw" type="text" id="colored" size="5"  value="{$content_bw|escape}" />
			</td>
			<td width="50%" class="podSettingsInfo">{translate key="plugins.generic.pod.coloredHelp"}</td>
		</tr>
		<tr valign="top">
			<td width="20%" class="label">{fieldLabel name="preface"  key="plugins.generic.pod.preface"}</td>
			<td width="30%" class="value">
				<textarea name="preface" cols="30" rows="4">{$preface|escape}</textarea>
			</td>
			<td width="50%" class="podSettingsInfo">{translate key="plugins.generic.pod.prefaceHelp"}</td>
		</tr>
		<tr valign="top">
			<td width="20%" class="label">{fieldLabel name="cover"  key="plugins.generic.pod.cover"}</td>
			<td width="30%" class="value">
				<input name="cover" type="file" id="cover" />
			</td>
			<td width="50%" class="podSettingsInfo">{translate key="plugins.generic.pod.coverHelp"}</td>
		</tr>
		<tr valign="top">
			<td width="20%"></td>
			<td width="30%">
				{if $cover}
					<span id="coverFile">{$cover|escape}</span><span id="deleteCover"><input type="checkbox" name="checkDeleteCover" />{translate key="common.delete"}</span>
				{/if}
			</td>
			<td width="50%"></td>
		</tr>
		<!--
		<tr valign="top" id="checkBusinessAllowed">
			<td width="20%" class="label"><em>{translate key="plugins.generic.pod.businessAllowed"}</em> <input type="checkbox" name="businessAllowed" id="businessAllowed" onclick="isBusinessAllowed()" {if ($currency && $price)}checked{/if} /></td>
			<td width="30%" class="value"></td>
			<td width="50%" class="podSettingsInfo"></td>
		</tr>
		<tr valign="top">
			<td width="20%" class="label">{fieldLabel name="price"  key="plugins.generic.pod.price"}</td>
			<td width="30%" class="value">
				<input name="price" type="text" id="price" class="opt_in" size="5"  value="{$price|escape}" {if !$price}disabled{/if} />
			</td>
			<td width="50%" class="podSettingsInfo">{translate key="plugins.generic.pod.priceHelp"}</td>
		</tr>
		<tr valign="top">
			<td width="20%" class="label">{fieldLabel name="currency"  key="plugins.generic.pod.currency"}</td>
			<td width="30%" class="value">
				<input name="currency" type="text" id="currency" class="opt_in" size="5"  value="{$currency|escape}" {if !$currency}disabled{/if} />
			</td>
			<td width="50%" class="podSettingsInfo">{translate key="plugins.generic.pod.currencyHelp"}</td>
		</tr>
		-->
		<!--
		<tr valign="top">
			<td width="20%" class="label">{fieldLabel name="failure_url" required="true" key="plugins.generic.pod.failure_url"}</td>
			<td width="30%" class="value">
				<input name="failure_url" type="text" id="failure_url" size="40"  value="{$failure_url|escape}" />
			</td>
			<td width="50%" class="podSettingsInfo">{translate key="plugins.generic.pod.failure_urlHelp"}</td>
		</tr>
		-->
		<tr>
			<td width="20%" class="contactperson">{translate key="plugins.generic.pod.contactperson"}</td>
			<td width="30%"></td>
			<td width="50%"></td>
		</tr>
		<tr valign="top">
			<td width="20%" class="label">{fieldLabel name="user_id"  key="plugins.generic.pod.user_id"}</td>
			<td width="30%" class="value">
				<input name="user_id" type="text" id="user_id" size="40"  value="{$user_id|escape}" />
			</td>
			<td width="50%" class="podSettingsInfo">{translate key="plugins.generic.pod.user_idHelp"}</td>
		</tr>
		<tr valign="top">
			<td width="20%" class="label">{fieldLabel name="firstname" key="plugins.generic.pod.firstname"}</td>
			<td width="30%" class="value">
				<input name="firstname" type="text" id="firstname" size="40"  value="{$firstname|escape}" />
			</td>
			<td width="50%" class="podSettingsInfo">{translate key="plugins.generic.pod.firstnameHelp"}</td>
		</tr>
		<tr valign="top">
			<td width="20%" class="label">{fieldLabel name="lastname"  key="plugins.generic.pod.lastname"}</td>
			<td width="30%" class="value">
				<input name="lastname" type="text" id="lastname" size="40"  value="{$lastname|escape}" />
			</td>
			<td width="50%" class="podSettingsInfo">{translate key="plugins.generic.pod.lastnameHelp"}</td>
		</tr>
		<tr valign="top">
			<td width="20%" class="label">{fieldLabel name="street"  key="plugins.generic.pod.street"}</td>
			<td width="30%" class="value">
				<input name="street" type="text" id="street" size="40"  value="{$street|escape}" />
			</td>
			<td width="50%" class="podSettingsInfo">{translate key="plugins.generic.pod.streetHelp"}</td>
		</tr>
		<tr valign="top">
			<td width="20%" class="label">{fieldLabel name="number"  key="plugins.generic.pod.number"}</td>
			<td width="30%" class="value">
				<input name="number" type="text" id="number" size="40"  value="{$number|escape}" />
			</td>
			<td width="50%" class="podSettingsInfo">{translate key="plugins.generic.pod.numberHelp"}</td>
		</tr>
		<tr valign="top">
			<td width="20%" class="label">{fieldLabel name="zip"  key="plugins.generic.pod.zip"}</td>
			<td width="30%" class="value">
				<input name="zip" type="text" id="zip" size="40"  value="{$zip|escape}" />
			</td>
			<td width="50%" class="podSettingsInfo">{translate key="plugins.generic.pod.zipHelp"}</td>
		</tr>
		<tr valign="top">
			<td width="20%" class="label">{fieldLabel name="town"  key="plugins.generic.pod.town"}</td>
			<td width="30%" class="value">
				<input name="town" type="text" id="town" size="40"  value="{$town|escape}" />
			</td>
			<td width="50%" class="podSettingsInfo">{translate key="plugins.generic.pod.townHelp"}</td>
		</tr>
		<tr valign="top">
			<td width="20%" class="label">{fieldLabel name="country"  key="plugins.generic.pod.country"}</td>
			<td width="30%" class="value">
				<input name="country" type="text" id="country" size="40"  value="{$country|escape}" />
			</td>
			<td width="50%" class="podSettingsInfo">{translate key="plugins.generic.pod.countryHelp"}</td>
		</tr>
		<tr valign="top">
			<td width="20%" class="label">{fieldLabel name="email"  key="plugins.generic.pod.email"}</td>
			<td width="30%" class="value">
				<input name="email" type="text" id="email" size="40"  value="{$email|escape}" />
			</td>
			<td width="50%" class="podSettingsInfo">{translate key="plugins.generic.pod.emailHelp"}</td>
		</tr>
		
	</table>

	<input type="hidden" name="currentCover" value="{$cover|escape}" />
	<input type="submit" name="save" class="button defaultButton" value="{translate key="common.save"}"/><input type="button" class="button" value="{translate key="common.cancel"}" onclick="history.go(-1)"/>
	</form>

	<p><span class="formRequired">{translate key="common.requiredField"}</span></p>
</div>
{include file="common/footer.tpl"}