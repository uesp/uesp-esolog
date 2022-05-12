from html2image import Html2Image
hti = Html2Image()
# hti.output_path = '/home/uesp/esoSkillImages/'

# hti.screenshot(url='https://www.python.org', save_as='python_org.png')
# hti.screenshot(html_file='https://esolog.uesp.net/skillTooltip.php?id=28988', css_file='https://esolog.uesp.net/resources/esoSkills_embed.css', save_as='test.png')
hti.screenshot(url='https://esolog.uesp.net/skillTooltip.php?id=28988', css_file='/home/uesp/www/esolog/resources/esoskills_embed.css', save_as='test.png')