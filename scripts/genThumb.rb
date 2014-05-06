# coding: utf-8
require 'date'
require 'pathname'
now = DateTime.now

baseDir = File.dirname(Pathname.new(__FILE__).realpath)

# 文件目录
videopath = File.expand_path("../Upload/OpusVideo",baseDir)

# 日志文件
logFile=File.expand_path("../App/Runtime/Logs/genThumb#{now.strftime("%Y%m")}.log",baseDir)

# 获取所有文件
files = Dir.entries(videopath)

counter = 0
logs = "#{now.strftime("%Y/%m/%d %H:%M:%S")} ===开始查询有无新视频===\n"

if(Dir.chdir(videopath))
	files.each do |file|
		# 仅对视频文件操作
		if file.downcase =~ /.(mov|mp4|mkv|flv)$/
			filename=file.split(/\./).first+".jpg"
			filename_mini=file.split(/\./).first+"_mini"+".jpg"
			now = DateTime.now
			# 如果文件不存在
			if(!files.index(filename))
				`ffmpeg -i #{file} -y -f image2 -ss 20 -vframes 1 #{filename}`
				File.chmod(0666,filename)
				logs=logs+"#{now.strftime("%Y/%m/%d %H:%M:%S")} 创建了#{filename}\n"
				logs=logs+"COMMAND   ffmpeg -i #{file} -y -f image2 -ss 20 -vframes 1 #{filename}\n"
				counter=counter.succ
			end
			if(!files.index(filename_mini))
				`ffmpeg -i #{file} -y -f image2 -ss 20 -vframes 1 -s cif #{filename_mini}`
				File.chmod(0666,filename_mini)
				logs=logs+"#{now.strftime("%Y/%m/%d %H:%M:%S")} 创建了#{filename_mini}\n"
				logs=logs+"COMMAND   ffmpeg -i #{file} -y -f image2 -ss 20 -vframes 1 -s cif #{filename_mini}\n"
				counter=counter.succ
			end
		end
	end
end


logs=logs+"#{now.strftime("%Y/%m/%d %H:%M:%S")} 创建完成，共创建#{counter}个图片文件\n"
# 写入日志
File.open(logFile,"a") do |thelogFile|
	thelogFile.print logs
end
