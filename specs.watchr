$exec    = "phpunit tests/unit"
$command = "notify-send -i '{image}' '{message}'"

watch('.*.php')  { |m| changed(m[0]) } 

def changed(file) 
	puts "\e[H\e[2J"  #clear console
	
	message = `phpcs --standard=tests/ --encoding=utf-8 #{file}`
	puts message
	if $?.to_i != 0
		image = "~/.watchr_images/failed.png"		
	else
		image = "~/.watchr_images/passed.png" 

		message = `#{$exec}`
		puts message

		if message.include?("OK (") and !message.include?("FAILURES")
			image = "~/.watchr_images/passed.png" 
		else
			image = "~/.watchr_images/failed.png"	
		end
	end

	cmd = $command.gsub('{image}', File.expand_path(image))
	cmd = cmd.gsub('{message}', message)
	system %(#{cmd} &)
end	