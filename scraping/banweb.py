import urllib2
import copy
import os
import banweb_to_php
from bs4 import BeautifulSoup

def getRetardedSelectSyntaxFromBanweb(selectTag):
	retval = []
	for subject in selectTag.findAll("option"):
		sid = subject["value"]
		ssubject = str(subject)
		sval = ssubject.split(">")[1].split("<")[0].strip()
		retval.append([sid, sval])
	return retval

def writeSelectToFile(selectArray, filename, title):
	f = open(filename, "w")
	f.write(title+" = [['")
	optionStrings = []
	for optionParts in selectArray:
		optionStrings.append("','".join(optionParts))
	f.write("'],\n\t['".join(optionStrings))
	f.write("']]")
	f.close()

class classList:
	headers = []
	classes = []
	
	def __init__(self):
		self.headers = []
		self.classes = []
		
	def getClasses(self):
		return self.classes
	def getHeaders(self):
		return self.headers
	
	def isClass(self, bs4tr):
		tds = bs4tr.findAll("td")
		if (len(self.headers) == 0):
			return False
		if (len(tds) == len(self.headers)):
			countWithContent = 0
			for td in tds:
				if (len(td.contents) == 0):
					continue
				text = td.contents[0]
				if (text.strip() != ""):
					countWithContent += 1
			if (float(countWithContent) / float(len(self.headers)) > 0.5):
				return True
		return False
	def addClass(self, bs4tr):
		if (self.isClass(bs4tr)):
			tds = bs4tr.findAll("td")
			classStats = {}
			for i in range(len(tds)):
				td = tds[i]
				if (len(td.contents) == 0):
					continue
				text = td.contents[0]
				classStats[self.headers[i]] = text
			self.classes.append(classStats)
			return True
		return False
	def setHeaders(self, bs4tr):
		self.headers = []
		ths = bs4tr.findAll("th")
		for th in ths:
			font = th.contents[0]
			text = font.contents[0]
			self.headers.append(text)
		return True

class term:
	semester = "" #eg "201330"
	classLists = {} # a dictionary of classlist objects, by subject
	subjects = [] # an array of arrays[shortname, longname]
	
	def __init__(self, semester):
		self.flush()
		self.subjects = []
		self.semester = semester
		
	def flush(self):
		self.semester = ""
		self.subjects = []
		self.classLists = []
	
	def getSemester(self):
		return self.semester

	def semesterAsSeason(self):
		semester = self.semester[4:6]+""
		semesterName = "";
		if (semester == "10"):
			semesterName = "Summer"
		elif (semester == "20"):
			semesterName = "Fall"
		elif (semester == "30"):
			semesterName = "Spring"
		return semesterName
	
	def getPrintedSemester(self):
		semesterName = self.semesterAsSeason()
		if (semesterName == ""):
			semesterName = "Unknown"
		return semesterName+" "+self.semester[:4]
	
	def _subjIndex(self, subject):
		i = self.subjects.index(subject)
		if (len(self.classLists) <= i):
			self.addSubject(subject)
		return i
	def getClassList(self, subject):
		return self.classLists[self._subjIndex(subject)]
	def getClasses(self, subject):
		return self.classLists[self._subjIndex(subject)].getClasses()
	def getHeaders(self, subject):
		return self.classLists[self._subjIndex(subject)].getHeaders()
	def addSubject(self, subject):
		if (not subject in self.subjects):
			self.subjects.append(subject)
			i = self.subjects.index(subject)
			self.classLists.append(classList())
	def getSubjects(self):
		retval = []
		for subject in self.subjects:
			retval.append(subject)
		return retval
	# requires a beautiful soup table row
	def addClass(self, subject, bs4tr):
		return self.classLists[self._subjIndex(subject)].addClass(bs4tr)
	# requires a beautiful soup table row
	def setHeaders(self, subject, bs4tr):
		return self.classLists[self._subjIndex(subject)].setHeaders(bs4tr)

url = "http://banweb7.nmt.edu/pls/PROD/hwzkcrof.p_uncgslctcrsoff"

page = urllib2.urlopen(url)

soup = BeautifulSoup(page)

nodes = soup.findAll("select")

termsList = [] # each term -> [yyyyss, "season year"]
subjects = [] # each subject -> ["abriviation", "full name"]

for node in nodes:
	nodeAttribs = dict(node.attrs)
	if nodeAttribs[u"name"] == u"p_term":
		termsList = getRetardedSelectSyntaxFromBanweb(node)[:]
	elif nodeAttribs[u"name"] == u"p_subj":
		subjects = getRetardedSelectSyntaxFromBanweb(node)[:]

writeSelectToFile(termsList, "banweb_terms.py", "terms")
writeSelectToFile(subjects, "banweb_subjects.py", "subjects")

terms = []

def getTerm(semester):
	t = term(semester[0])
	for subject in subjects:
		subjectName = subject[0]
		url = "http://banweb7.nmt.edu/pls/PROD/hwzkcrof.P_UncgSrchCrsOff?p_term="+t.getSemester()+"&p_subj="+subjectName.replace(" ", "%20")
		print url
		page = urllib2.urlopen(url)
		soup = BeautifulSoup(page)
		trs = soup.findAll("tr")
		trs = trs[1:] #discard the retarded row that banweb is retarded about
		t.addSubject(subject)
		if (len(trs) == 0):
			continue
		headersRow = trs[0]
		t.setHeaders(subject, headersRow)
		classesRows = trs[1:]
		for tr in classesRows:
			t.addClass(subject, tr)
	return t

latestYear = 0
latestSemester = 0

for t in termsList:
	year = int(t[0][0:4])
	semester = int(t[0][4:6])
	if (year > latestYear and semester > latestSemester):
		latestYear = year
		latestSemester = semester
	
for t in termsList:
	semester = t[0]
	filename = "sem_"+semester+".py"
	if (os.path.exists(filename) and semester != latestYear.__str__()+latestSemester.__str__()):
		continue
	terms.append(copy.copy(getTerm(t)))

for t in terms:
	name = t.getPrintedSemester()
	subjects = t.getSubjects()
	classes = []
	for subj in subjects:
		classes.append(t.getClasses(subj))
	f = open("sem_"+t.getSemester()+".py", "w")
	f.write("name = \""+name+"\"\n")
	f.write("subjects = "+subjects.__str__()+"\n")
	f.write("classes = "+classes.__str__())
	f.close()

banweb_to_php.main()
