import imgkit
import pymysql
import re
from pathlib import Path

TABLE_SUFFIX = "38pts"
OUTPUT_PATH = "/tmp/skills38pts/"

print("Outputting all skill tooltip images for update '{0}' to '{1}'...".format(TABLE_SUFFIX, OUTPUT_PATH))

dbUser = ""
dbPassword = ""
dbHost = "db2.uesp.net"
dbDatabase = "uesp_esolog";

with open("/home/uesp/secrets/esolog.secrets", "r") as text_file:
	secrets = text_file.read()
	
	result = re.search("uespEsoLogReadUser.*=.*'(.*)';", secrets)
	if (result): dbUser = result.group(1)
	
	result = re.search("uespEsoLogReadPW.*=.*'(.*)';", secrets)
	if (result): dbPassword = result.group(1)

if (dbUser == "" or dbPassword == ""):
	print("Failed to find database user/password in secrets file!")
	exit()

Path(OUTPUT_PATH).mkdir(parents=True, exist_ok=True)

connection = pymysql.connect(host = dbHost, user = dbUser, password = dbPassword, database = dbDatabase, cursorclass = pymysql.cursors.DictCursor)

skills = []

with connection:
	print("\tConnected to database {0}...".format(dbDatabase))
	
	with connection.cursor() as cursor:
		query = "SELECT * FROM `skillTree{0}`;".format(TABLE_SUFFIX)
		cursor.execute(query)
		
		skills = cursor.fetchall()
		print("\tLoaded {0} skills from skillTree{1} table!".format(len(skills), TABLE_SUFFIX))

uniqueSkills = []

for skill in skills:
	type = skill['type']
	rank = int(skill['rank'])
	maxRank = int(skill['maxRank'])
	
	if (type != "Passive" and rank >= 9):
		rank -= 8;
	elif (type != "Passive" and rank >= 5):
		rank -= 4;
	
	skill['rank'] = rank
	
	if (rank == maxRank):
		uniqueSkills.append(skill)

print("\tFound {0} unique skills!".format(len(uniqueSkills)))

imgOptions = {'width': 380, 'disable-smart-width': '', 'quality' : '50', 'log-level' : 'none' }

for skill in uniqueSkills:
	skillType, sep, skillLine = skill['skillTypeName'].partition('::')
	if (skillLine == ""): skillType, sep, skillLine = skill['skillTypeName'].partition(':')
	
	skillName = skill['name']
	abilityId = skill['abilityId']
	
	url = "https://esolog.uesp.net/skillTooltip.php?fullhtml=1&id=" + str(abilityId)
	path = OUTPUT_PATH + skillType + "/" + skillLine + "/"
	filename = path + skillName + ".png"
	
	print("\t{0}:{1}:{2} saving to {3}".format(skillType, skillLine, skillName, filename))
	
	Path(path).mkdir(parents=True, exist_ok=True)
	
	imgkit.from_url(url, filename, options=imgOptions)



	