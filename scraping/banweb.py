import urllib2
import copy
import os
import banweb_to_php
from bs4 import BeautifulSoup
import argparse

def getRetardedSelectSyntaxFromBanweb(selectTag):
	retval = []
	for subject in selectTag.findAll("option"):
		sid = subject["value"]
		ssubject = str(subject)
		sval = ssubject.split(">")[1].split("<")[0].strip()
		retval.append([sid, sval])
	return retval

def writeSelectToFile(selectArray, filename, path, title):
	f = open(path+filename, "w")
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
	
	def determinClassType(self, bs4tr):
		tds = bs4tr.findAll("td")
		if (len(self.headers) == 0):
			return "none"
		if (len(tds) == len(self.headers)):
			countWithContent = 0
			for td in tds:
				if (len(td.contents) == 0):
					continue
				text = td.contents[0]
				if (text.strip() != ""):
					countWithContent += 1
			ratio = float(countWithContent) / float(len(self.headers))
			if (ratio > 0.5):
				return "class"
			if (countWithContent > 2 or ratio > 0.5):
				return "subclass"
		return "none"
	def addClass(self, bs4tr):
		classType = self.determinClassType(bs4tr)
		if (classType == "class" or classType == "subclass"):
			tds = bs4tr.findAll("td")
			classStats = {}
			for i in range(len(tds)):
				td = tds[i]
				if (len(td.contents) == 0):
					continue
				text = td.contents[0]
				classStats[self.headers[i]] = text
			classStats["Type"] = classType
			self.classes.append(classStats)
			print_verbose("adding class: "+str(classStats))
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

def getTerm(semester, subjects, parser):
	t = term(semester[0])
	for subject in subjects:
		subjectName = subject[0]
		if (type(parser.subject) == type("")):
			if (parser.subject != subjectName):
				continue
		t.addSubject(subject)
		if (subjectName == "CUSTOM"):
			continue
		url = "http://banweb7.nmt.edu/pls/PROD/hwzkcrof.P_UncgSrchCrsOff?p_term="+t.getSemester()+"&p_subj="+subjectName.replace(" ", "%20")
		print url
		page = urllib2.urlopen(url)
		soup = BeautifulSoup(page)
		trs = soup.findAll("tr")
		trs = trs[1:] #discard the retarded row that banweb is retarded about
		print_verbose("adding subject "+subjectName)
		if (len(trs) == 0):
			print_verbose("no table rows...no classes subject")
			continue
		headersRow = trs[0]
		print_verbose("adding headers "+str(headersRow))
		t.setHeaders(subject, headersRow)
		classesRows = trs[1:]
		for tr in classesRows:
			t.addClass(subject, tr)
	return t

def main(parser):
	url = "http://banweb7.nmt.edu/pls/PROD/hwzkcrof.p_uncgslctcrsoff"

	page = urllib2.urlopen(url)
	
	soup = BeautifulSoup(page)

	path = ""
	if (type(parser.path) == type("")):
		path = parser.path

	# load all terms and subjects from banweb
	nodes = soup.findAll("select")
	termsList = [] # each term -> [yyyyss, "season year"]
	subjects = [] # each subject -> ["abriviation", "full name"]
	for node in nodes:
		nodeAttribs = dict(node.attrs)
		if nodeAttribs[u"name"] == u"p_term":
			termsList = getRetardedSelectSyntaxFromBanweb(node)[:]
		elif nodeAttribs[u"name"] == u"p_subj":
			subjects = getRetardedSelectSyntaxFromBanweb(node)[:]
	subjects.append(["CUSTOM","Custom"])

	# account for terms that have been listed before but
	# have since been removed from banweb
	import banweb_terms
	for t in banweb_terms.terms:
		if not t in termsList:
			termsList.append(t)
	termsList.sort()

	# save the new listing of terms and subjects
	writeSelectToFile(termsList, "banweb_terms.py", path, "terms")
	writeSelectToFile(subjects, "banweb_subjects.py", path, "subjects")

	# only process the terms that the user wants to
	if (type(parser.semester) == type("")):
		newtermsList = []
		for t in termsList:
			if (t[0] == parser.semester):
				newtermsList.append(t)
		termsList = newtermsList
	print termsList

	terms = []

	# load the latest three semester available on banweb
	numLatestSemesters = 3
	latestYear = 0
	latestSemester = 0
	latestYearSemester = "201320"
	latestYearSemesters = [""]*numLatestSemesters
	latestYearSemesters[0] = latestYearSemester

	for t in termsList:
		year = int(t[0][0:4])
		semester = int(t[0][4:6])
		if (t[0] > latestYearSemester):
			latestYear = year
			latestSemester = semester
			latestYearSemester = t[0]
			for i in range(numLatestSemesters-1):
				latestYearSemesters[numLatestSemesters-i-1] = latestYearSemesters[numLatestSemesters-i-2]
			latestYearSemesters[0] = latestYearSemester
	
	for t in termsList:
		semester = t[0]
		filename = "sem_"+semester+".py"
		if (os.path.exists(path+filename) and semester not in latestYearSemesters):
			continue
		terms.append(copy.copy(getTerm(t, subjects, parser)))

	for t in terms:
		name = t.getPrintedSemester()
		subjects = t.getSubjects()
		classes = []
		for subj in subjects:
			classes.append(t.getClasses(subj))
		filename = "sem_"+t.getSemester()+".py"
		print_verbose("writing to file "+path+filename)
		try:
			f = open(path+filename, "w")
			f.write("name = \""+name+"\"\n")
			f.write("subjects = "+subjects.__str__()+"\n")
			f.write("classes = "+classes.__str__())
			f.close()
		except IOError as (errno,strerror):
			print "I/O error({0}): {1}".format(errno, strerror)

	banweb_to_php.main(parser)

verbose = False
def print_verbose(arg):
	if verbose:
		print arg

if __name__ == "__main__":
	parser = argparse.ArgumentParser(description="Loads current class data from banweb.nmt.edu and saves it to local files for quick access (caching) and readable formats.")
	parser.add_argument("--semester", type=str, help="choose the semester to load (eg '201430')")
	parser.add_argument("--subject", type=str, help="choose the subject to load (eg 'CSE')")
	parser.add_argument("--path", type=str, help="choose the location to save semester files to (must end in a slash, eg '/home/usr/stuff/')")
	parser.add_argument("-v", action="store_true", dest="verbose", help="Print out verbose text about what is happening")
	p = parser.parse_args()
	verbose = p.verbose
	main(p)
