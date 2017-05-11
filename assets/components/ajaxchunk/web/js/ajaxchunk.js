//ajaxchunk.storage={};
ajaxchunk.addData = function(name,data)
{
	if(ajaxchunk.storage[name]===undefined)return;
	if(data instanceof FormData)
	{
		ajaxchunk.storage[name].formdata = data;
		return;
	}
	for(var key in data)
	{
		if(!data.hasOwnProperty(key))continue;
		if(data[key]===null)
		{
			delete(ajaxchunk.storage[name].data[key]);
			continue;
		}
		ajaxchunk.storage[name].data[key] = data[key];
	}
};
ajaxchunk.load = function(name,trying)
{
	trying = trying||0;
	var processData = true;
	var contentType = 'application/x-www-form-urlencoded; charset=UTF-8';
	var data = ajaxchunk.storage[name].data;
	ajaxchunk.storage[name].config.timestamp = Date.now();
	data.ajaxchunk_name = name;
	data.timestamp = ajaxchunk.storage[name].config.timestamp;
	var formdata = ajaxchunk.storage[name].formdata;
	if(formdata instanceof FormData)
	{
		for(var key in data){formdata.set(key,data[key]);}
		data = formdata;
		processData = false;
		contentType = false;
	}
	
	var chunk = $('#ajaxchunk_'+name);
	chunk.addClass('loading');
	$.ajax
	({
		type:'post',
		url:ajaxchunk.connector_url,
		data:data,
		processData:processData,
		contentType:contentType,
		success:function(response,textStatus,jqXHR)
		{
			if(response.timestamp!==ajaxchunk.storage[name].config.timestamp)return;
			if(response.success)
			{
				chunk.html(response.content);
			}
			else
			{
				chunk.html(response.message);
			}
			chunk.removeClass('loading');
			if(ajaxchunk.storage[name].config.afterLoad!==undefined && ajaxchunk.storage[name].config.afterLoad!=='')
			{
				if(typeof(ajaxchunk.storage[name].config.afterLoad)==='function')
				{
					ajaxchunk.storage[name].config.afterLoad(chunk,name,ajaxchunk.storage[name]);
				}
				else if(typeof(window[ajaxchunk.storage[name].config.afterLoad])==='function')
				{
					window[ajaxchunk.storage[name].config.afterLoad](chunk,name,ajaxchunk.storage[name]);
				}
			}
		},
		error:function(jqXHR,textStatus,errorThrown)
		{
			if(trying<5){
				trying += 1;
				ajaxchunk.load(name,trying);
			}
			else
			{
				chunk.removeClass('loading');
				var message = ajaxchunk.lang.notavail;
				alert(message);
			}
		},
		dataType:'json'
	});
};
