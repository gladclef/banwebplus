#! /usr/bin/python3

import urllib3
import copy
import os
import banweb_to_php
import bs4
import argparse
import importlib
import time
import json

# global stuff
http = urllib3.PoolManager()

def getSelectSyntaxFromBanweb(selectTag):
	retval = []
	for subject in selectTag.findAll("option"):
		sid = subject["value"]
		ssubject = str(subject)
		sval = ssubject.split(">")[1].split("<")[0].strip()
		retval.append([sid, sval])
	return retval

def writeVarToFile(varname, varval, path, filename):
	f = open(path+filename, "w")
	f.write("{} = {}".format(varname, str(varval)))
	f.close()

class classList:
	def __init__(self):
		self.headers = []
		self.classes = []
		
	def getClasses(self):
		return self.classes

	def getHeaders(self):
		return self.headers
	
	def determineClassType(self, bs4tr):
		tds = bs4tr.findAll("td")
		if (len(self.headers) == 0):
			return "none"
		if (len(tds) == len(self.headers)):
			countWithContent = 0
			for td in tds:
				if (len(td.contents) == 0):
					continue
				content = td.contents[0]
				if isinstance(content, bs4.element.Tag): # example "Bookstore Link" hyperlinks
					countWithContent += 1
				elif (content.strip() != ""):
					countWithContent += 1

			ratio = float(countWithContent) / float(len(self.headers))
			if (ratio > 0.5):
				return "class"
			if (countWithContent > 2):
				return "subclass"
		return "none"

	def addClass(self, bs4tr):
		classType = self.determineClassType(bs4tr)
		if (classType == "class" or classType == "subclass"):
			tds = bs4tr.findAll("td")
			classStats = {}
			for i in range(len(tds)):
				td = tds[i]
				if (len(td.contents) == 0):
					continue
				content = td.contents[0]
				if not isinstance(content, bs4.element.NavigableString): # example "Bookstore Link" hyperlinks
					continue
				classStats[self.headers[i]] = content
			classStats["Type"] = classType
			self.classes.append(classStats)
			print_verbose("adding class: "+str(classStats), 2)
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
	def __init__(self, semester : str):
		# example semester: "201330"
		self.flush()
		self.semester = semester
		self.subjects = [] # an array of tuples(shortname, longname)
		self.classLists = {} # a dictionary of classlist objects, by subject index
		
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
		return self.subjects.index(subject)
	def getClassList(self, subject):
		return self.classLists[self._subjIndex(subject)]
	def getClasses(self, subject):
		return self.classLists[self._subjIndex(subject)].getClasses()
	def getHeaders(self, subject):
		return self.classLists[self._subjIndex(subject)].getHeaders()
	def addSubject(self, subject):
		if (not subject in self.subjects):
			self.subjects.append(subject)
			self.classLists[self._subjIndex(subject)] = classList()
	def getSubjects(self):
		return copy.copy(self.subjects)
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
		print_verbose(url, 2)
		page = http.request('GET', url)
		soup = bs4.BeautifulSoup(page.data, 'html.parser')
		trs = soup.findAll("tr")
		trs = trs[1:] #discard the retarded row that banweb is retarded about
		print_verbose("adding subject "+subjectName, 1)
		if (len(trs) == 0):
			print_verbose("no table rows...no classes subject", 2)
			continue
		headersRow = trs[0]
		print_verbose("adding headers "+str(headersRow), 2)
		t.setHeaders(subject, headersRow)
		classesRows = trs[1:]
		for tr in classesRows:
			t.addClass(subject, tr)
		time.sleep(1) # don't spam the nice people
	return t

def main(parser):
	# load the front page with all the terms and subjects
	page = http.request('GET', 'http://banweb7.nmt.edu/pls/PROD/hwzkcrof.p_uncgslctcrsoff')
	soup = bs4.BeautifulSoup(page.data, 'html.parser')

	path = ""
	if (type(parser.path) == type("")):
		path = parser.path

	# load all terms and subjects from banweb
	nodes = soup.findAll("select")
	termsList = [] # each term -> [yyyyss, "season year"]
	subjects = [] # each subject -> ["abbreviation", "full name"]
	for node in nodes:
		nodeAttribs = dict(node.attrs)
		if nodeAttribs[u"name"] == u"p_term":
			termsList = getSelectSyntaxFromBanweb(node)[:]
		elif nodeAttribs[u"name"] == u"p_subj":
			subjects = getSelectSyntaxFromBanweb(node)[:]
	subjects.append(["CUSTOM","Custom"])

	# account for terms that have been listed before but
	# have since been removed from banweb
	try: ModuleNotFoundError
	except NameError: ModuleNotFoundError = ImportError
	try:
		banweb_terms = importlib.import_module('banweb_terms')
		unmentioned_terms = filter(lambda t: t not in termsList, banweb_terms.terms)
		termsList += list(unmentioned_terms)
	except ModuleNotFoundError as err:
		# in case the banweb_terms.py file doesn't exist yet
		pass
	termsList.sort()

	# save the new listing of terms and subjects
	writeVarToFile("terms", termsList,   path, "banweb_terms.py")
	writeVarToFile("subjects", subjects, path, "banweb_subjects.py")

	# only process the terms that the user wants to
	if (type(parser.semester) == type("")):
		newtermsList = []
		for t in termsList:
			if (t[0] == parser.semester):
				newtermsList.append(t)
		termsList = newtermsList
	print_verbose(termsList, 2)

	terms = []

	# always reload the latest three semester available on banweb
	numLatestSemesters = 3
	latestYear = 0
	latestSemester = 0
	latestYearSemester = "201410"
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
		print_verbose("Adding semester {}".format(semester), 1)
		terms.append(copy.copy(getTerm(t, subjects, parser)))
		time.sleep(5) # don't spam the nice people

	for t in terms:
		name = t.getPrintedSemester()
		subjects = t.getSubjects()
		classes = []
		for subj in subjects:
			classes.append(t.getClasses(subj))
		filename = "sem_"+t.getSemester()+".py"
		print_verbose("writing to file "+path+filename, 2)
		try:
			f = open(path+filename, "w")
			f.write("name = \""+name+"\"\n")
			f.write("subjects = "+json.dumps(subjects)+"\n")
			f.write("classes = "+json.dumps(classes)+"\n")
			f.close()
		except IOError as err:
			print(err)

	banweb_to_php.main(parser)

verbose = 0
def print_verbose(strval, verbosity):
	if verbose >= verbosity:
		print(strval)

if __name__ == "__main__":
	parser = argparse.ArgumentParser(description="Loads current class data from banweb.nmt.edu and saves it to local files for quick access (caching) and readable formats.")
	parser.add_argument("--semester", type=str, help="choose the semester to load (eg '201430')")
	parser.add_argument("--subject", type=str, help="choose the subject to load (eg 'CSE')")
	parser.add_argument("--path", type=str, help="choose the location to save semester files to (must end in a slash, eg '/home/usr/stuff/')")
	parser.add_argument("-v", action="count", dest="verbose", help="Print out verbose text about what is happening")

	p = parser.parse_args()
	verbose = 0 if p.verbose is None else p.verbose
	main(p)
