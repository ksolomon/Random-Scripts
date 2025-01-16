tell application "System Events"
	activate
	set theWidth to display dialog "Enter the width" default answer "650"
	set theWidth to the text returned of theWidth as real
end tell
global theWidth
tell application "Finder"
	set some_items to selection as list
	repeat with aItem in some_items
		set contents of aItem to aItem as alias
	end repeat
end tell
repeat with i in some_items
	try
		rescale_and_save(i)
	end try
end repeat

to rescale_and_save(this_item)
	tell application "Image Events"
		launch
		set the target_width to theWidth
		-- open the image file
		set this_image to open this_item
		
		set typ to this_image's file type
		
		copy dimensions of this_image to {current_width, current_height}
		if current_width is greater than target_width then
			if current_width is greater than current_height then
				scale this_image to size target_width
			else
				-- figure out new height
				-- y2 = (y1 * x2) / x1
				set the new_height to (current_height * target_width) / current_width
				scale this_image to size new_height
			end if
		end if
		
		tell application "Finder"
			set file_name to name of this_item
			set file_location to (container of this_item as string)
			set new_item to (file_location & file_name)
			save this_image in new_item as typ
		end tell
		
	end tell
end rescale_and_save
